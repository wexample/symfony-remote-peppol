<?php

namespace Wexample\SymfonyRemotePeppol\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyRemotePeppol\Class\PeppolTransmission;
use Wexample\SymfonyRemotePeppol\Event\DocumentReceivedEvent;
use Wexample\SymfonyRemotePeppol\Event\TransmissionUpdatedEvent;
use Wexample\SymfonyRemotePeppol\Exception\InvalidWebhookException;
use Wexample\SymfonyRemotePeppol\Service\AccessPointRegistry;

/**
 * Where access points post their news; each item becomes an event.
 */
#[Route(path: '/_peppol', name: 'peppol_')]
class PeppolWebhookController
{
    public function __construct(
        private readonly AccessPointRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[Route(path: '/webhook/{provider}', name: 'webhook', methods: ['POST'])]
    public function webhook(
        string $provider,
        Request $request
    ): Response {
        $parser = $this->registry->findWebhookParser($provider);

        if (null === $parser) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $headers = [];
        foreach ($request->headers->all() as $name => $values) {
            $headers[strtolower($name)] = (string) ($values[0] ?? '');
        }

        try {
            $items = $parser->parseWebhook($request->getContent(), $headers);
        } catch (InvalidWebhookException) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        foreach ($items as $item) {
            $this->eventDispatcher->dispatch($item instanceof PeppolTransmission
                ? new TransmissionUpdatedEvent($item)
                : new DocumentReceivedEvent($item));
        }

        // A listener that throws answers 500: the access point posts again later.
        return new Response('', Response::HTTP_OK);
    }
}
