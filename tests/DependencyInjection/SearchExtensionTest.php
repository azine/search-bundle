<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle\DependencyInjection;

use EWZ\Bundle\SearchBundle\DependencyInjection\EWZSearchExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class SearchExtensionTest extends TestCase
{
    private const ANALYZER = 'Zend\\Search\\Lucene\\Analysis\\Analyzer\\Common\\TextNum\\CaseInsensitive';

    public function testLoadsDefaultConfigurationAndPublicServices(): void
    {
        $container = $this->createContainer();
        (new EWZSearchExtension())->load([], $container);

        self::assertSame([], $container->getParameter('lucene.indices'));
        self::assertSame(self::ANALYZER, $container->getParameter('lucene.analyzer'));
        self::assertSame(
            '%kernel.project_dir%/var/cache/%kernel.environment%/lucene/index',
            $container->getParameter('lucene.index.path'),
        );
        self::assertSame(
            'EWZ\\Bundle\\SearchBundle\\Lucene\\LuceneSearch',
            $container->getParameter('ewz_search.lucene.search.class'),
        );

        self::assertTrue($container->getDefinition('ewz_search.lucene')->isPublic());
        self::assertTrue($container->getDefinition('ewz_search.lucene.manager')->isPublic());

        $container->compile();

        self::assertTrue($container->has('ewz_search.lucene'));
        self::assertTrue($container->has('ewz_search.lucene.manager'));
    }

    public function testLoadsNamedIndexConfiguration(): void
    {
        $container = $this->createContainer();
        (new EWZSearchExtension())->load([
            [
                'indices' => [
                    'content' => [
                        'path' => '/tmp/azine-search-content',
                        'analyzer' => self::ANALYZER,
                    ],
                    'people' => [
                        'path' => '/tmp/azine-search-people',
                        'analyzer' => self::ANALYZER,
                    ],
                ],
                'path' => '/tmp/azine-search-legacy',
                'analyzer' => self::ANALYZER,
            ],
        ], $container);

        self::assertSame([
            'content' => [
                'path' => '/tmp/azine-search-content',
                'analyzer' => self::ANALYZER,
            ],
            'people' => [
                'path' => '/tmp/azine-search-people',
                'analyzer' => self::ANALYZER,
            ],
        ], $container->getParameter('lucene.indices'));
        self::assertSame('/tmp/azine-search-legacy', $container->getParameter('lucene.index.path'));
    }

    private function createContainer(): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.project_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'test',
        ]));
    }
}
