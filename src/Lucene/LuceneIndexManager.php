<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\Lucene;

final class LuceneIndexManager
{
    /** @var array<string, LuceneSearch> */
    private array $indices = [];

    /**
     * @param array<string, array{path: string, analyzer: string}> $indices
     * @param class-string<LuceneSearch>                           $indexClass
     */
    public function __construct(array $indices, string $indexClass)
    {
        if (!is_a($indexClass, LuceneSearch::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'The configured Lucene search class "%s" must extend %s.',
                $indexClass,
                LuceneSearch::class,
            ));
        }

        foreach ($indices as $name => $config) {
            $index = new $indexClass($config['path'], $config['analyzer']);
            $this->indices[$name] = $index;
        }
    }

    public function getIndex(string $indexName): ?LuceneSearch
    {
        return $this->indices[$indexName] ?? null;
    }
}
