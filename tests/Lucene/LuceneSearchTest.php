<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle\Lucene;

use EWZ\Bundle\SearchBundle\Lucene\Document;
use EWZ\Bundle\SearchBundle\Lucene\Field;
use EWZ\Bundle\SearchBundle\Lucene\LuceneSearch;
use PHPUnit\Framework\TestCase;
use Zend\Search\Lucene\Index;

final class LuceneSearchTest extends TestCase
{
    private string $indexDirectory;
    private ?LuceneSearch $search = null;

    protected function setUp(): void
    {
        $this->indexDirectory = sys_get_temp_dir().'/azine-search-bundle-'.bin2hex(random_bytes(8));
        $this->search = new LuceneSearch($this->indexDirectory);
    }

    protected function tearDown(): void
    {
        $this->search = null;
        $this->removeDirectory($this->indexDirectory);
        parent::tearDown();
    }

    public function testCreatesALuceneIndex(): void
    {
        self::assertInstanceOf(Index::class, $this->search()->getIndex());
    }

    public function testAddsAndCommitsADocument(): void
    {
        $this->search()->addDocument($this->document('1', 'First article', 'searchable content'));
        $this->search()->updateIndex();

        self::assertSame(1, $this->search()->getIndex()->count());
        self::assertCount(1, $this->search()->find('searchable'));
    }

    public function testReturnsTheMoreRelevantDocumentFirst(): void
    {
        $great = $this->document(
            '1',
            'This is a great article about great things',
            'There are many great things to discuss.',
            '123',
        );
        $unrelated = $this->document(
            '2',
            'Ramblings of a mad person',
            'This document discusses something unrelated.',
            '234',
        );
        $good = $this->document(
            '3',
            'This is a good article about good things',
            'There are good things to discuss, including one great example.',
            '345',
        );

        $this->search()->addDocument($great);
        $this->search()->addDocument($unrelated);
        $this->search()->addDocument($good);
        $this->search()->updateIndex();

        $results = $this->search()->find('great');

        self::assertCount(2, $results);
        self::assertSame('123', $results[0]->getDocument()->getFieldValue('id'));
    }

    public function testDeletesTheDocumentWithTheSameKey(): void
    {
        $document = $this->document('delete-me', 'Disposable article', 'deletiontoken');
        $this->search()->addDocument($document);
        $this->search()->updateIndex();
        self::assertCount(1, $this->search()->find('deletiontoken'));

        $this->search()->deleteDocument($document);
        $this->search()->updateIndex();

        self::assertCount(0, $this->search()->find('deletiontoken'));
    }

    public function testUpdatesByReplacingTheDocumentWithTheSameKey(): void
    {
        $this->search()->addDocument($this->document('same-key', 'Old article', 'legacytoken'));
        $this->search()->updateIndex();

        $this->search()->updateDocument($this->document('same-key', 'New article', 'replacementtoken'));
        $this->search()->updateIndex();

        self::assertCount(0, $this->search()->find('legacytoken'));
        $replacementResults = $this->search()->find('replacementtoken');
        self::assertCount(1, $replacementResults);
        self::assertSame('New article', $replacementResults[0]->title);
    }

    public function testReopensAndSearchesAnExistingIndex(): void
    {
        $this->search()->addDocument($this->document('persisted', 'Persistent article', 'reopentoken'));
        $this->search()->updateIndex();
        $this->search = null;

        $this->search = new LuceneSearch($this->indexDirectory);
        $results = $this->search()->find('reopentoken');

        self::assertCount(1, $results);
        self::assertSame('persisted', $results[0]->key);
    }

    private function document(string $key, string $title, string $body, string $id = '1'): Document
    {
        $document = new Document();
        $document->addField(Field::keyword('key', $key));
        $document->addField(Field::keyword('url', 'https://example.test/'.$key));
        $document->addField(Field::unIndexed('id', $id));
        $document->addField(Field::text('title', $title));
        $document->addField(Field::unStored('body', $body));

        return $document;
    }

    private function search(): LuceneSearch
    {
        self::assertNotNull($this->search);

        return $this->search;
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
