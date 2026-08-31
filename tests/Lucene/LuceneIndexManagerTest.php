<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle\Lucene;

use EWZ\Bundle\SearchBundle\Lucene\LuceneIndexManager;
use EWZ\Bundle\SearchBundle\Lucene\LuceneSearch;
use PHPUnit\Framework\TestCase;

final class LuceneIndexManagerTest extends TestCase
{
    private const ANALYZER = 'Zend\\Search\\Lucene\\Analysis\\Analyzer\\Common\\TextNum\\CaseInsensitive';

    /** @var string[] */
    private array $directories = [];
    private ?LuceneIndexManager $manager = null;

    protected function tearDown(): void
    {
        $this->manager = null;

        foreach ($this->directories as $directory) {
            $this->removeDirectory($directory);
        }

        parent::tearDown();
    }

    public function testBuildsAndReturnsNamedIndices(): void
    {
        $contentPath = $this->newIndexPath('content');
        $peoplePath = $this->newIndexPath('people');

        $this->manager = new LuceneIndexManager([
            'content' => ['path' => $contentPath, 'analyzer' => self::ANALYZER],
            'people' => ['path' => $peoplePath, 'analyzer' => self::ANALYZER],
        ], LuceneSearch::class);

        self::assertInstanceOf(LuceneSearch::class, $this->manager->getIndex('content'));
        self::assertInstanceOf(LuceneSearch::class, $this->manager->getIndex('people'));
        self::assertNull($this->manager->getIndex('missing'));
        self::assertDirectoryExists($contentPath);
        self::assertDirectoryExists($peoplePath);
    }

    public function testRejectsAnUnrelatedConfiguredIndexClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend');

        new LuceneIndexManager([], \stdClass::class);
    }

    private function newIndexPath(string $name): string
    {
        $path = sys_get_temp_dir().'/azine-search-manager-'.$name.'-'.bin2hex(random_bytes(8));
        $this->directories[] = $path;

        return $path;
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
