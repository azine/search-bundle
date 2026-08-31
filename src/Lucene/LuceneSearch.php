<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\Lucene;

use Zend\Search\Lucene\Analysis\Analyzer as AnalyzerInterface;
use Zend\Search\Lucene\Analysis\Analyzer\Analyzer as AnalyzerManager;
use Zend\Search\Lucene\Document as ZendDocument;
use Zend\Search\Lucene\Index;
use Zend\Search\Lucene\Index\Term;
use Zend\Search\Lucene\Search\QueryHit;

class LuceneSearch
{
    protected Index $index;

    /**
     * @param class-string<AnalyzerInterface>|null $analyzer
     */
    public function __construct(string $luceneIndexPath, ?string $analyzer = null)
    {
        if (null !== $analyzer) {
            if (!is_a($analyzer, AnalyzerInterface::class, true)) {
                throw new \InvalidArgumentException(sprintf(
                    'The configured analyzer "%s" must implement %s.',
                    $analyzer,
                    AnalyzerInterface::class,
                ));
            }

            AnalyzerManager::setDefault(new $analyzer());
        }

        $this->index = file_exists($luceneIndexPath)
            ? Lucene::open($luceneIndexPath)
            : Lucene::create($luceneIndexPath);
    }

    public function getIndex(): Index
    {
        return $this->index;
    }

    public function addDocument(ZendDocument $document): void
    {
        $this->deleteDocument($document);
        $this->index->addDocument($document);
    }

    public function updateIndex(): void
    {
        $this->index->commit();
        $this->index->optimize();
    }

    /**
     * @return QueryHit[]
     */
    public function find(mixed $query, mixed ...$arguments): array
    {
        return $this->index->find($query, ...$arguments);
    }

    public function updateDocument(ZendDocument $document): void
    {
        $this->addDocument($document);
    }

    public function deleteDocument(ZendDocument $document): void
    {
        $term = new Term((string) $document->getField('key')->value, 'key');

        foreach ($this->index->termDocs($term) as $id) {
            $this->index->delete($id);
        }
    }
}
