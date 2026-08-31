<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle\Lucene;

use EWZ\Bundle\SearchBundle\Lucene\Document;
use EWZ\Bundle\SearchBundle\Lucene\Field;
use PHPUnit\Framework\TestCase;
use Zend\Search\Lucene\Exception\InvalidArgumentException;

final class LuceneDocumentTest extends TestCase
{
    public function testReturnsTheFactoryTypeForEveryBundleField(): void
    {
        $document = new Document();
        $document->addField(Field::binary('binary', 'value'));
        $document->addField(Field::keyword('keyword', 'value'));
        $document->addField(Field::text('text', 'value'));
        $document->addField(Field::unIndexed('unindexed', 'value'));
        $document->addField(Field::unStored('unstored', 'value'));

        self::assertSame('Binary', $document->getFieldType('binary'));
        self::assertSame('Keyword', $document->getFieldType('keyword'));
        self::assertSame('Text', $document->getFieldType('text'));
        self::assertSame('UnIndexed', $document->getFieldType('unindexed'));
        self::assertSame('UnStored', $document->getFieldType('unstored'));
    }

    public function testUnknownFieldFailsWithTheZendSearchException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field name "missing" not found in document.');

        (new Document())->getFieldType('missing');
    }
}
