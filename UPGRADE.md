# Upgrade to 2.0

Version 2.0 is the PHP 8.5 / Symfony 7.4 compatibility release.

## Runtime and dependency changes

- Upgrade the application to PHP 8.5 and Symfony 7.4.
- Require `excelwebzone/search-bundle:^2.0`.
- Require/allow `excelwebzone/zend-search:^2.0`; development branch aliases are no longer needed.
- Enable `iconv` and `mbstring`.
- Register `EWZSearchBundle` in `config/bundles.php` rather than a legacy `AppKernel`.

## Configuration changes

Move configuration to `config/packages/ewz_search.yaml`.

The default paths now use `%kernel.project_dir%` and Symfony's `var/` directory because the removed `%kernel.root_dir%` parameter is not available in Symfony 7.4:

- single index: `%kernel.project_dir%/var/cache/%kernel.environment%/lucene/index`
- default named-index path: `%kernel.project_dir%/var/lucene/%kernel.environment%/defaultIndex`

Applications that need to keep an existing index location should configure `path` explicitly.

The public service IDs remain:

- `ewz_search.lucene` for the original single-index API;
- `ewz_search.lucene.manager` for named indexes.

Inject these services explicitly from `config/services.yaml`.

## Document replacement contract

`LuceneSearch::addDocument()` and `updateDocument()` delete an existing document with the same key before adding the replacement. Every document passed to these methods must therefore contain an indexed `key` field.

## Existing indexes

The underlying Zend Search 2.0 release does not change the Lucene implementation or on-disk format. Existing indexes can be reused. Before production rollout:

1. back up the index directory;
2. open and query a production-shaped copy on PHP 8.5;
3. run representative searches and compare result ordering;
4. verify a clean rebuild from the application database;
5. document the chosen reuse or rebuild deployment path.

## Removed tooling

The PHP 5.6/7.x Travis configuration and the legacy PHPUnit bootstrap were removed. Use:

```bash
composer update
composer lint
composer test
```
