<?php

namespace Wexample\SymfonyRemotePeppol\Enum;

/**
 * Where a sent document stands on the network, as the access point reports it.
 * Whether the buyer accepts the invoice is another message (an Invoice Response),
 * not a transmission status.
 */
enum TransmissionStatus: string
{
    /** Taken by our access point, not delivered yet. */
    case Queued = 'queued';

    /** Received by the receiver's access point, which signed for it. */
    case Delivered = 'delivered';

    /** Undeliverable: receiver unknown, document refused by validation, network error given up on. */
    case Failed = 'failed';

    public function isFinal(): bool
    {
        return self::Queued !== $this;
    }
}
