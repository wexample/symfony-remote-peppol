<?php

namespace Wexample\SymfonyRemotePeppol\Interface;

/**
 * What every access point capability shares: a name, unique among providers.
 */
interface AccessPointProviderInterface
{
    public function getName(): string;
}
