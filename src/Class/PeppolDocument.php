<?php

namespace Wexample\SymfonyRemotePeppol\Class;

use InvalidArgumentException;

/**
 * A business document to send: who sends it, who receives it, what kind it is
 * (document type and process, as the Peppol directory lists them), and the XML.
 */
final readonly class PeppolDocument
{
    public const string DOCUMENT_TYPE_SCHEME = 'busdox-docid-qns';
    public const string PROCESS_SCHEME = 'cenbii-procid-ubl';

    public const string BILLING_INVOICE = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1';
    public const string BILLING_CREDIT_NOTE = 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2::CreditNote##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1';
    public const string BILLING_PROCESS = 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0';

    public function __construct(
        public PeppolParticipant $sender,
        public PeppolParticipant $receiver,
        public string $documentType,
        public string $process,
        public string $content,
    ) {
    }

    /**
     * A Peppol BIS Billing 3.0 invoice or credit note, its type told by the XML's root.
     */
    public static function billing(
        PeppolParticipant $sender,
        PeppolParticipant $receiver,
        string $ubl
    ): self {
        $documentType = match (true) {
            1 === preg_match('/<(?:[\w-]+:)?Invoice[\s>]/', $ubl) => self::BILLING_INVOICE,
            1 === preg_match('/<(?:[\w-]+:)?CreditNote[\s>]/', $ubl) => self::BILLING_CREDIT_NOTE,
            default => throw new InvalidArgumentException('This is neither a UBL invoice nor a UBL credit note.'),
        };

        return new self($sender, $receiver, $documentType, self::BILLING_PROCESS, $ubl);
    }
}
