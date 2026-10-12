<?php

namespace Wexample\SymfonyRemotePeppol\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wexample\SymfonyRemotePeppol\Class\ReceivedDocument;

/**
 * A document arrived, by webhook or by collecting an inbox. A listener that
 * cannot handle it throws: the document then stays in the inbox, to be handed
 * over again.
 */
class DocumentReceivedEvent extends Event
{
    public function __construct(
        public readonly ReceivedDocument $document,
    ) {
    }
}
