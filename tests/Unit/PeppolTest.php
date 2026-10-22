<?php

namespace Wexample\SymfonyRemotePeppol\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Wexample\SymfonyRemotePeppol\Class\FakeAccessPoint;
use Wexample\SymfonyRemotePeppol\Class\PeppolDocument;
use Wexample\SymfonyRemotePeppol\Class\PeppolParticipant;
use Wexample\SymfonyRemotePeppol\Controller\PeppolWebhookController;
use Wexample\SymfonyRemotePeppol\Enum\TransmissionStatus;
use Wexample\SymfonyRemotePeppol\Event\DocumentReceivedEvent;
use Wexample\SymfonyRemotePeppol\Event\TransmissionUpdatedEvent;
use Wexample\SymfonyRemotePeppol\Exception\AccessPointException;
use Wexample\SymfonyRemotePeppol\Service\AccessPointRegistry;
use Wexample\SymfonyRemotePeppol\Service\InboxCollector;

class PeppolTest extends TestCase
{
    private const string INVOICE = '<?xml version="1.0"?><Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"/>';

    private const string CREDIT_NOTE = '<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"></CreditNote>';

    public function testAParticipantIsReadWithOrWithoutItsScheme(): void
    {
        $participant = PeppolParticipant::fromString('iso6523-actorid-upis::0208:0123456749');

        $this->assertSame('0208', $participant->icd);
        $this->assertSame('0208:0123456749', (string) $participant);
        $this->assertSame('iso6523-actorid-upis::0208:0123456749', $participant->toUri());
        $this->assertFalse($participant->equals(PeppolParticipant::fromString('9925:be0123456749')));
        $this->assertTrue(PeppolParticipant::fromString('9925:BE0123456749')->equals(PeppolParticipant::fromString('9925:be0123456749')));

        $this->expectException(InvalidArgumentException::class);
        PeppolParticipant::fromString('BE0123456749');
    }

    public function testABillingDocumentIsTypedByItsRoot(): void
    {
        $sender = new PeppolParticipant('0208', '0123456749');
        $receiver = new PeppolParticipant('0208', '0417497106');

        $this->assertSame(PeppolDocument::BILLING_INVOICE, PeppolDocument::billing($sender, $receiver, self::INVOICE)->documentType);
        $this->assertSame(PeppolDocument::BILLING_CREDIT_NOTE, PeppolDocument::billing($sender, $receiver, self::CREDIT_NOTE)->documentType);

        $this->expectException(InvalidArgumentException::class);
        PeppolDocument::billing($sender, $receiver, '<Order/>');
    }

    public function testOnlyAReachableReceiverIsSentTo(): void
    {
        $accessPoint = new FakeAccessPoint();
        $receiver = new PeppolParticipant('0208', '0417497106');
        $document = PeppolDocument::billing(new PeppolParticipant('0208', '0123456749'), $receiver, self::INVOICE);

        try {
            $accessPoint->send($document);
            $this->fail('Sent to someone not on Peppol.');
        } catch (AccessPointException) {
        }

        $accessPoint->register($receiver);
        $transmission = $accessPoint->send($document);

        $this->assertSame(TransmissionStatus::Queued, $transmission->status);
        $this->assertSame(TransmissionStatus::Delivered, $accessPoint->deliver($transmission->reference)->status);
        $this->assertSame(TransmissionStatus::Delivered, $accessPoint->getTransmission($transmission->reference)->status);
    }

    public function testTheSenderIsTheOneConfiguredOrTheOnlyOne(): void
    {
        $first = new FakeAccessPoint('first');
        $second = new FakeAccessPoint('second');

        $this->assertSame($first, (new AccessPointRegistry([$first]))->findSender());
        $this->assertNull((new AccessPointRegistry([$first, $second]))->findSender());
        $this->assertSame($second, (new AccessPointRegistry([$first, $second], 'second'))->findSender());
        $this->assertSame($second, (new AccessPointRegistry([$first, $second], 'second'))->findDirectory());
        $this->assertNull((new AccessPointRegistry())->findSender());
    }

    public function testCollectingHandsEachDocumentOverThenAcknowledgesIt(): void
    {
        $accessPoint = new FakeAccessPoint();
        $accessPoint->receive(new PeppolParticipant('0208', '0417497106'), new PeppolParticipant('0208', '0123456749'), self::INVOICE);
        $dispatcher = new EventDispatcher();
        $received = [];
        $dispatcher->addListener(DocumentReceivedEvent::class, function (DocumentReceivedEvent $event) use (&$received) {
            $received[] = $event->document;
        });

        $this->assertSame(1, (new InboxCollector(new AccessPointRegistry([$accessPoint]), $dispatcher))->collect());
        $this->assertTrue($received[0]->isBilling());
        $this->assertSame([], $accessPoint->fetch());
    }

    public function testADocumentNotHandledStaysInTheInbox(): void
    {
        $accessPoint = new FakeAccessPoint();
        $accessPoint->receive(new PeppolParticipant('0208', '0417497106'), new PeppolParticipant('0208', '0123456749'), self::INVOICE);
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(DocumentReceivedEvent::class, fn () => throw new RuntimeException('Books closed.'));

        try {
            (new InboxCollector(new AccessPointRegistry([$accessPoint]), $dispatcher))->collect();
            $this->fail('No exception.');
        } catch (RuntimeException) {
        }

        $this->assertCount(1, $accessPoint->fetch());
    }

    public function testAWebhookBecomesEvents(): void
    {
        $accessPoint = (new FakeAccessPoint())->register(new PeppolParticipant('0208', '0417497106'));
        $reference = $accessPoint->send(PeppolDocument::billing(new PeppolParticipant('0208', '0123456749'), new PeppolParticipant('0208', '0417497106'), self::INVOICE))->reference;
        $dispatcher = new EventDispatcher();
        $updated = null;
        $dispatcher->addListener(TransmissionUpdatedEvent::class, function (TransmissionUpdatedEvent $event) use (&$updated) {
            $updated = $event->transmission;
        });
        $controller = new PeppolWebhookController(new AccessPointRegistry([$accessPoint]), $dispatcher);
        $payload = json_encode(['reference' => $reference, 'status' => 'failed', 'message' => 'Unknown receiver']);

        $this->assertSame(404, $controller->webhook('other', new Request(content: $payload))->getStatusCode());
        $this->assertSame(400, $controller->webhook('fake', new Request(server: ['HTTP_X_FAKE_SIGNATURE' => 'wrong'], content: $payload))->getStatusCode());
        $this->assertSame(200, $controller->webhook('fake', new Request(server: ['HTTP_X_FAKE_SIGNATURE' => 'fake-secret'], content: $payload))->getStatusCode());
        $this->assertSame(TransmissionStatus::Failed, $updated->status);
        $this->assertSame('Unknown receiver', $updated->message);
    }
}
