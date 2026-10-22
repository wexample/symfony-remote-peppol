<?php

namespace Wexample\SymfonyRemotePeppol\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;
use Wexample\SymfonyRemotePeppol\Interface\AccessPointProviderInterface;

class WexampleSymfonyRemotePeppolExtension extends AbstractWexampleSymfonyExtension
{
    public const string TAG_PROVIDER = 'wexample_symfony_remote_peppol.provider';

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('wexample_symfony_remote_peppol.sender', $config['sender']);

        // Implementing any capability is enough to be an access point.
        $container
            ->registerForAutoconfiguration(AccessPointProviderInterface::class)
            ->addTag(self::TAG_PROVIDER);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
