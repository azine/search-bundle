# EWZ Search Bundle

`excelwebzone/search-bundle` integrates the maintained Zend Search Lucene fork into Symfony applications.

## Requirements

- PHP 8.5
- Symfony 7.4
- `excelwebzone/zend-search` 2.x
- PHP extensions `iconv` and `mbstring`

## Installation

```bash
composer require excelwebzone/search-bundle:^2.0
```

Register the bundle in `config/bundles.php`:

```php
<?php

return [
    // ...
    EWZ\Bundle\SearchBundle\EWZSearchBundle::class => ['all' => true],
];
```

## Configuration

### Named indexes

```yaml
# config/packages/ewz_search.yaml
ewz_search:
    indices:
        content:
            path: '%kernel.project_dir%/var/lucene/%kernel.environment%/content'
            analyzer: 'Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive'
        people:
            path: '%kernel.project_dir%/var/lucene/%kernel.environment%/people'
            analyzer: 'Zend\Search\Lucene\Analysis\Analyzer\Common\Utf8\CaseInsensitive'
```

Access a named index through the public manager service:

```yaml
# config/services.yaml
services:
    App\Search\ContentSearch:
        arguments:
            $indexManager: '@ewz_search.lucene.manager'
```

```php
<?php

use EWZ\Bundle\SearchBundle\Lucene\Document;
use EWZ\Bundle\SearchBundle\Lucene\Field;
use EWZ\Bundle\SearchBundle\Lucene\LuceneIndexManager;

final class ContentSearch
{
    public function __construct(private readonly LuceneIndexManager $indexManager)
    {
    }

    public function index(string $id, string $title, string $body): void
    {
        $search = $this->indexManager->getIndex('content');
        if (null === $search) {
            throw new \LogicException('The content search index is not configured.');
        }

        $document = new Document();
        $document->addField(Field::keyword('key', $id));
        $document->addField(Field::text('title', $title));
        $document->addField(Field::unStored('body', $body));

        $search->addDocument($document);
        $search->updateIndex();
    }
}
```

Every document added through `LuceneSearch` must contain a `key` field. Adding or updating a document replaces an existing document with the same key.

### Original single-index configuration

The original service and configuration remain supported:

```yaml
# config/packages/ewz_search.yaml
ewz_search:
    path: '%kernel.project_dir%/var/lucene/%kernel.environment%/default'
    analyzer: 'Zend\Search\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive'
```

Inject the service explicitly:

```yaml
services:
    App\Search\LegacySearchConsumer:
        arguments:
            $search: '@ewz_search.lucene'
```

## Searching

```php
$results = $search->find('Symfony');
```

The returned values are Zend Search `QueryHit` objects. Stored fields can be read as properties or through the hit document.

## Index compatibility

Version 2.0 uses `excelwebzone/zend-search:^2.0`. That release keeps the Lucene implementation and on-disk index format unchanged while adding PHP 8.5 support. Existing indexes can therefore be reused, but deployments should still back up and test a production-shaped index before switching runtimes.

## Development

```bash
composer update
composer lint
composer test
```

GitHub Actions validates stable and lowest supported dependency sets on PHP 8.5, compiles the Symfony dependency-injection configuration, and exercises index creation, add/update/delete, search and reopen behavior.

See [UPGRADE.md](UPGRADE.md) for the 1.x to 2.0 migration steps.
