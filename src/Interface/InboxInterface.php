<?php

namespace Wexample\SymfonyRemotePeppol\Interface;

use Wexample\SymfonyRemotePeppol\Class\ReceivedDocument;

/**
 * An access point keeping received documents until they are collected.
 */
interface InboxInterface extends AccessPointProviderInterface
{
    /**
     * Documents received and not acknowledged yet.
     *
     * @return list<ReceivedDocument>
     */
    public function fetch(): array;

    /**
     * Handled: the access point stops handing it over.
     */
    public function acknowledge(ReceivedDocument $document): void;
}
