<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private const DEFAULT_ANALYZER = 'Zend\\Search\\Lucene\\Analysis\\Analyzer\\Common\\TextNum\\CaseInsensitive';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ewz_search');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('indices')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')
                                ->defaultValue('%kernel.project_dir%/var/lucene/%kernel.environment%/defaultIndex')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('analyzer')
                                ->defaultValue(self::DEFAULT_ANALYZER)
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                // Kept for applications that use the original single-index configuration.
                ->scalarNode('analyzer')
                    ->defaultValue(self::DEFAULT_ANALYZER)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('path')
                    ->defaultValue('%kernel.project_dir%/var/cache/%kernel.environment%/lucene/index')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
