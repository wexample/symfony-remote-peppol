<?php

namespace Wexample\SymfonyRemotePeppol\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wexample\SymfonyRemotePeppol\Class\PeppolTransmission;

/**
 * A sent document moved: delivered, or failed.
 */
class TransmissionUpdatedEvent extends Event
{
    public function __construct(
        public readonly PeppolTransmission $transmission,
    ) {
    }
}
