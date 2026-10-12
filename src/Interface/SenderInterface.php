<?php

namespace Wexample\SymfonyRemotePeppol\Interface;

use Wexample\SymfonyRemotePeppol\Class\PeppolDocument;
use Wexample\SymfonyRemotePeppol\Class\PeppolTransmission;
use Wexample\SymfonyRemotePeppol\Exception\AccessPointException;

/**
 * An access point that sends documents and tells where each one stands.
 */
interface SenderInterface extends AccessPointProviderInterface
{
    /**
     * @throws AccessPointException When the access point refuses or cannot be reached.
     */
    public function send(PeppolDocument $document): PeppolTransmission;

    public function getTransmission(string $reference): PeppolTransmission;
}
