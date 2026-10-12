<?php

namespace Wexample\SymfonyRemotePeppol\Class;

use DateTimeImmutable;
use Wexample\SymfonyRemotePeppol\Enum\TransmissionStatus;

/**
 * A sent document as the access point knows it, under its reference.
 */
final readonly class PeppolTransmission
{
    /**
     * @param string|null $message Why it failed, when it did.
     */
    public function __construct(
        public string $provider,
        public string $reference,
        public TransmissionStatus $status,
        public DateTimeImmutable $at,
        public ?string $message = null,
    ) {
    }
}
