<?php

namespace Wexample\SymfonyRemotePeppol\Class;

use InvalidArgumentException;
use Stringable;

/**
 * Someone reachable on Peppol: an identifier in a registry named by its ISO 6523
 * code (ICD), `0208:0123456749` for a Belgian enterprise number, `0009:…` for a
 * French SIRET, `9925:BE0123456749` for a Belgian VAT number.
 */
final readonly class PeppolParticipant implements Stringable
{
    public const string SCHEME = 'iso6523-actorid-upis';

    /**
     * @param string $icd The registry's ISO 6523 code, four digits.
     */
    public function __construct(
        public string $icd,
        public string $identifier,
    ) {
        if (! preg_match('/^\d{4}$/', $icd)) {
            throw new InvalidArgumentException(sprintf('"%s" is no ISO 6523 code.', $icd));
        }

        if ('' === trim($identifier)) {
            throw new InvalidArgumentException('A participant needs an identifier.');
        }
    }

    /**
     * `0208:0123456749`, with or without the `iso6523-actorid-upis::` scheme before it.
     */
    public static function fromString(string $value): self
    {
        $value = preg_replace('/^'.preg_quote(self::SCHEME, '/').'::/i', '', trim($value));
        $parts = explode(':', $value, 2);

        if (2 !== count($parts)) {
            throw new InvalidArgumentException(sprintf('"%s" is no Peppol participant.', $value));
        }

        return new self($parts[0], $parts[1]);
    }

    public function __toString(): string
    {
        return $this->icd.':'.$this->identifier;
    }

    public function toUri(): string
    {
        return self::SCHEME.'::'.$this;
    }

    /**
     * Peppol identifiers are case-insensitive.
     */
    public function equals(self $other): bool
    {
        return 0 === strcasecmp((string) $this, (string) $other);
    }
}
