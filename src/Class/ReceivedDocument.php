<?php

namespace Wexample\SymfonyRemotePeppol\Class;

use DateTimeImmutable;

/**
 * A document that reached us through the network, waiting to be handled.
 */
final readonly class ReceivedDocument
{
    public function __construct(
        public string $provider,
        public string $reference,
        public PeppolParticipant $sender,
        public PeppolParticipant $receiver,
        public string $documentType,
        public string $content,
        public DateTimeImmutable $receivedAt,
    ) {
    }

    public function isBilling(): bool
    {
        return in_array($this->documentType, [PeppolDocument::BILLING_INVOICE, PeppolDocument::BILLING_CREDIT_NOTE], true);
    }
}
