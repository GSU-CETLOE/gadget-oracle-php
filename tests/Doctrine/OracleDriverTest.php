<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Doctrine;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\Driver\AbstractOracleDriver;
use Doctrine\DBAL\Driver\OCI8\Exception\InvalidConfiguration;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Gadget\Oracle\Doctrine\OracleDriver;
use PHPUnit\Framework\TestCase;

/**
 * Opening a real connection needs ext-oci8 and a database, so these tests
 * stop at the checks the driver makes before it calls into the extension.
 */
final class OracleDriverTest extends TestCase
{
    public function testIsAnOracleDriver(): void
    {
        self::assertInstanceOf(AbstractOracleDriver::class, new OracleDriver());
    }


    public function testUsesAnOraclePlatformWithAnOracleStyleDateFormat(): void
    {
        $platform = (new OracleDriver())->getDatabasePlatform(new StaticServerVersionProvider('19.0.0.0.0'));

        self::assertInstanceOf(OraclePlatform::class, $platform);
        self::assertSame('d-M-y H.i.s.u A', $platform->getDateFormatString());
    }


    /**
     * The session mode is given explicitly because its default, OCI_NO_AUTO_COMMIT,
     * is only defined when ext-oci8 is loaded.
     */
    public function testRefusesAPersistentAndExclusiveConnectionAtTheSameTime(): void
    {
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage(
            'The "persistent" parameter and the "exclusive" driver option are mutually exclusive'
        );

        (new OracleDriver())->connect([
            'user' => 'scott',
            'password' => 'tiger',
            'dbname' => 'orcl',
            'sessionMode' => 0,
            'persistent' => true,
            'driverOptions' => ['exclusive' => true],
        ]);
    }
}
