<?php

namespace Wexample\SymfonyRemotePeppol\Interface;

use Wexample\SymfonyRemotePeppol\Class\PeppolTransmission;
use Wexample\SymfonyRemotePeppol\Class\ReceivedDocument;
use Wexample\SymfonyRemotePeppol\Exception\InvalidWebhookException;

/**
 * An access point posting its news: a transmission that moved, a document received.
 */
interface WebhookParserInterface extends AccessPointProviderInterface
{
    /**
     * Verifies the signature and reduces the call to what it reports; empty for
     * events that concern neither.
     *
     * @param array<string, string> $headers Lower-cased header names.
     * @return list<PeppolTransmission|ReceivedDocument>
     *
     * @throws InvalidWebhookException When the signature does not verify.
     */
    public function parseWebhook(
        string $payload,
        array $headers
    ): array;
}
