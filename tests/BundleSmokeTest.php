<?php

declare(strict_types=1);

namespace EWZ\Tests\Bundle\SearchBundle;

use EWZ\Bundle\SearchBundle\EWZSearchBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BundleSmokeTest extends TestCase
{
    public function testBundleCanBeInstantiatedOnSymfony74(): void
    {
        $bundle = new EWZSearchBundle();

        self::assertInstanceOf(Bundle::class, $bundle);
        self::assertSame('EWZSearchBundle', $bundle->getName());
    }
}
