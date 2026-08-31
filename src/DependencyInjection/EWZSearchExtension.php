<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class EWZSearchExtension extends Extension
{
    /**
     * @param array<array-key, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{
         *     indices: array<string, array{path: string, analyzer: string}>,
         *     analyzer: string,
         *     path: string
         * } $config
         */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('lucene.indices', $config['indices']);
        $container->setParameter('lucene.analyzer', $config['analyzer']);
        $container->setParameter('lucene.index.path', $config['path']);
    }
}
