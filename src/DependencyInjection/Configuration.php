<?php

namespace Wexample\SymfonyRemotePeppol\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_remote_peppol');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('sender')->defaultNull()->info('The access point that sends, by name; the only one installed when null.')->end()
            ->end();

        return $treeBuilder;
    }
}
