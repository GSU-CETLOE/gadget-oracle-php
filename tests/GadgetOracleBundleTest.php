<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests;

use Gadget\Oracle\DependencyInjection\GadgetOracleExtension;
use Gadget\Oracle\GadgetOracleBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GadgetOracleBundleTest extends TestCase
{
    public function testIsASymfonyBundle(): void
    {
        self::assertInstanceOf(Bundle::class, new GadgetOracleBundle());
    }


    public function testExposesTheGadgetOracleExtension(): void
    {
        $extension = (new GadgetOracleBundle())->getContainerExtension();

        self::assertInstanceOf(GadgetOracleExtension::class, $extension);
        self::assertSame('gadget_oracle', $extension->getAlias());
    }
}
