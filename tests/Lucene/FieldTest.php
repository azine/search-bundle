<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle\Lucene;

use EWZ\Bundle\SearchBundle\Lucene\Field;
use PHPUnit\Framework\TestCase;

final class FieldTest extends TestCase
{
    public function testFactoriesExposeTheExpectedFieldTypesAndFlags(): void
    {
        $binary = Field::binary('binary', 'value');
        $keyword = Field::keyword('keyword', 'value');
        $text = Field::text('text', 'value');
        $unIndexed = Field::unIndexed('unindexed', 'value');
        $unStored = Field::unStored('unstored', 'value');

        self::assertSame('Binary', $binary->getType());
        self::assertTrue($binary->isStored);
        self::assertFalse($binary->isIndexed);
        self::assertTrue($binary->isBinary);

        self::assertSame('Keyword', $keyword->getType());
        self::assertTrue($keyword->isStored);
        self::assertTrue($keyword->isIndexed);
        self::assertFalse($keyword->isTokenized);

        self::assertSame('Text', $text->getType());
        self::assertTrue($text->isStored);
        self::assertTrue($text->isIndexed);
        self::assertTrue($text->isTokenized);

        self::assertSame('UnIndexed', $unIndexed->getType());
        self::assertTrue($unIndexed->isStored);
        self::assertFalse($unIndexed->isIndexed);

        self::assertSame('UnStored', $unStored->getType());
        self::assertFalse($unStored->isStored);
        self::assertTrue($unStored->isIndexed);
        self::assertTrue($unStored->isTokenized);
    }
}
