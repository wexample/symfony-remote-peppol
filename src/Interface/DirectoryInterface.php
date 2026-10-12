<?php

namespace Wexample\SymfonyRemotePeppol\Interface;

use Wexample\SymfonyRemotePeppol\Class\PeppolParticipant;

/**
 * Who is on Peppol, and what they accept: the SML/SMP lookup, through the access
 * point that already knows how.
 */
interface DirectoryInterface extends AccessPointProviderInterface
{
    /**
     * The document types the participant accepts; null when it is not on Peppol.
     *
     * @return list<string>|null
     */
    public function findDocumentTypes(PeppolParticipant $participant): ?array;
}
