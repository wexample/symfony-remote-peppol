<?php

namespace Wexample\SymfonyRemotePeppol\Service;

use Psr\EventDispatcher\EventDispatcherInterface;
use Wexample\SymfonyRemotePeppol\Event\DocumentReceivedEvent;

/**
 * Collects every inbox: each document is handed to the application
 * (DocumentReceivedEvent), then acknowledged. A listener that throws leaves its
 * document in the inbox, so nothing received is lost.
 */
class InboxCollector
{
    public function __construct(
        private readonly AccessPointRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return int How many documents were handled.
     */
    public function collect(): int
    {
        $count = 0;

        foreach ($this->registry->getInboxes() as $inbox) {
            foreach ($inbox->fetch() as $document) {
                $this->eventDispatcher->dispatch(new DocumentReceivedEvent($document));
                $inbox->acknowledge($document);
                ++$count;
            }
        }

        return $count;
    }
}
