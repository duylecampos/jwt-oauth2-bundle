<?php

namespace JwtOAuth2Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jwt_o_auth2', 'array');

        $rootNode
            ->children()
                ->arrayNode('event')
                ->end()
                ->arrayNode('access_token_repository')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('public_key')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('file')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
