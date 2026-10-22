<?php

namespace Wexample\SymfonyRemotePeppol\Class;

use DateTimeImmutable;
use Wexample\SymfonyRemotePeppol\Enum\TransmissionStatus;
use Wexample\SymfonyRemotePeppol\Exception\AccessPointException;
use Wexample\SymfonyRemotePeppol\Exception\InvalidWebhookException;
use Wexample\SymfonyRemotePeppol\Interface\DirectoryInterface;
use Wexample\SymfonyRemotePeppol\Interface\InboxInterface;
use Wexample\SymfonyRemotePeppol\Interface\SenderInterface;
use Wexample\SymfonyRemotePeppol\Interface\WebhookParserInterface;

/**
 * An in-memory access point offering every capability, for tests and demos.
 *
 * Only registered participants are reachable. Sent documents stay queued until
 * deliver() or fail(), which return the transmission as a webhook would report it.
 * receive() puts a document in the inbox. Its webhook takes JSON
 * `{"reference": "...", "status": "delivered"}`, signed by the `x-fake-signature`
 * header holding the configured secret.
 */
class FakeAccessPoint implements SenderInterface, InboxInterface, WebhookParserInterface, DirectoryInterface
{
    /** @var array<string, list<string>> Participant (lowercase) → document types */
    private array $directory = [];

    /** @var array<string, PeppolTransmission> */
    private array $transmissions = [];

    /** @var array<string, ReceivedDocument> */
    private array $inbox = [];

    /** @var list<PeppolDocument> */
    public array $sent = [];

    private int $sequence = 0;

    public function __construct(
        private readonly string $name = 'fake',
        private readonly string $secret = 'fake-secret',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param list<string> $documentTypes
     */
    public function register(
        PeppolParticipant $participant,
        array $documentTypes = [PeppolDocument::BILLING_INVOICE, PeppolDocument::BILLING_CREDIT_NOTE]
    ): static {
        $this->directory[strtolower((string) $participant)] = $documentTypes;

        return $this;
    }

    public function findDocumentTypes(PeppolParticipant $participant): ?array
    {
        return $this->directory[strtolower((string) $participant)] ?? null;
    }

    public function send(PeppolDocument $document): PeppolTransmission
    {
        if (! in_array($document->documentType, $this->findDocumentTypes($document->receiver) ?? [], true)) {
            throw new AccessPointException(sprintf('%s does not accept this document on Peppol.', $document->receiver));
        }

        $this->sent[] = $document;
        $reference = $this->name.'_'.++$this->sequence;

        return $this->transmissions[$reference] = new PeppolTransmission($this->name, $reference, TransmissionStatus::Queued, new DateTimeImmutable());
    }

    public function getTransmission(string $reference): PeppolTransmission
    {
        return $this->transmissions[$reference] ?? throw new AccessPointException(sprintf('No transmission "%s".', $reference));
    }

    public function deliver(string $reference): PeppolTransmission
    {
        return $this->move($reference, TransmissionStatus::Delivered);
    }

    public function fail(
        string $reference,
        string $message = 'Rejected by the receiving access point.'
    ): PeppolTransmission {
        return $this->move($reference, TransmissionStatus::Failed, $message);
    }

    public function receive(
        PeppolParticipant $sender,
        PeppolParticipant $receiver,
        string $ubl
    ): ReceivedDocument {
        $document = PeppolDocument::billing($sender, $receiver, $ubl);
        $reference = $this->name.'_in_'.++$this->sequence;

        return $this->inbox[$reference] = new ReceivedDocument($this->name, $reference, $sender, $receiver, $document->documentType, $ubl, new DateTimeImmutable());
    }

    public function fetch(): array
    {
        return array_values($this->inbox);
    }

    public function acknowledge(ReceivedDocument $document): void
    {
        unset($this->inbox[$document->reference]);
    }

    public function parseWebhook(
        string $payload,
        array $headers
    ): array {
        if (! hash_equals($this->secret, $headers['x-fake-signature'] ?? '')) {
            throw new InvalidWebhookException('Bad signature.');
        }

        $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        return [$this->move($data['reference'], TransmissionStatus::from($data['status']), $data['message'] ?? null)];
    }

    private function move(
        string $reference,
        TransmissionStatus $status,
        ?string $message = null
    ): PeppolTransmission {
        $this->getTransmission($reference);

        return $this->transmissions[$reference] = new PeppolTransmission($this->name, $reference, $status, new DateTimeImmutable(), $message);
    }
}
