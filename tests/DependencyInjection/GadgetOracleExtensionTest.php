<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\DependencyInjection;

use Gadget\Oracle\Command\OracleCommand;
use Gadget\Oracle\Command\SqlLoaderCommand;
use Gadget\Oracle\Command\SqlPlusCommand;
use Gadget\Oracle\DependencyInjection\GadgetOracleExtension;
use Gadget\Oracle\Doctrine\OracleDriver;
use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\OracleOptions;
use Gadget\Oracle\Input\SqlLoaderOptions;
use Gadget\Oracle\Input\SqlPlusOptions;
use Gadget\Process\ProcessShellEnv;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GadgetOracleExtensionTest extends TestCase
{
    /**
     * A container with the extension loaded into it.
     *
     * @return ContainerBuilder
     */
    private static function loadedContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new GadgetOracleExtension())->load([], $container);
        return $container;
    }


    /**
     * A compiled container that can hand out the given services, with env()
     * parameters resolved against the current environment. A ProcessShellEnv
     * is registered because the commands need one and this package does not
     * provide it.
     *
     * @param class-string ...$publicServices
     * @return ContainerBuilder
     */
    private static function compiledContainer(string ...$publicServices): ContainerBuilder
    {
        $container = self::loadedContainer();
        $container->register(ProcessShellEnv::class)->setAutowired(true);
        foreach ($publicServices as $class) {
            $container->getDefinition($class)->setPublic(true);
        }
        $container->compile(true);
        return $container;
    }


    /**
     * Fetches a service and fails the test if it is not the expected class.
     *
     * @template T of object
     * @param ContainerBuilder $container
     * @param class-string<T> $class
     * @return T
     */
    private static function service(ContainerBuilder $container, string $class): object
    {
        $service = $container->get($class);
        if (!$service instanceof $class) {
            self::fail("$class was not built by the container");
        }
        return $service;
    }


    /**
     * @return \Generator<string, array{class-string}>
     */
    public static function providesRegisteredServices(): \Generator
    {
        yield SqlPlusCommand::class => [SqlPlusCommand::class];
        yield SqlLoaderCommand::class => [SqlLoaderCommand::class];
        yield SqlPlusOptions::class => [SqlPlusOptions::class];
        yield SqlLoaderOptions::class => [SqlLoaderOptions::class];
        yield LogonOptions::class => [LogonOptions::class];
        yield OracleDriver::class => [OracleDriver::class];
    }


    /**
     * @param class-string $class
     */
    #[DataProvider('providesRegisteredServices')]
    public function testRegistersAnAutowiredService(string $class): void
    {
        $container = self::loadedContainer();

        self::assertTrue($container->hasDefinition($class), "$class should be registered");
        $definition = $container->getDefinition($class);
        self::assertTrue($definition->isAutowired());
        self::assertTrue($definition->isAutoconfigured());
    }


    /**
     * @return \Generator<string, array{class-string}>
     */
    public static function providesAbstractClasses(): \Generator
    {
        yield OracleCommand::class => [OracleCommand::class];
        yield OracleOptions::class => [OracleOptions::class];
    }


    /**
     * @param class-string $class
     */
    #[DataProvider('providesAbstractClasses')]
    public function testDoesNotRegisterTheAbstractBases(string $class): void
    {
        self::assertFalse(self::loadedContainer()->hasDefinition($class), "$class should not be registered");
    }


    public function testBindsTheLogonOptionsToTheOracleEnvironmentVariables(): void
    {
        $definition = self::loadedContainer()->getDefinition(LogonOptions::class);

        self::assertSame('%env(default::ORACLE_USER)%', $definition->getArgument('$username'));
        self::assertSame('%env(default::ORACLE_PASS)%', $definition->getArgument('$password'));
        self::assertSame('%env(default::ORACLE_CONN)%', $definition->getArgument('$connectIdentifier'));
    }


    /**
     * @return \Generator<string, array{class-string<OracleCommand>}>
     */
    public static function providesCommands(): \Generator
    {
        yield SqlPlusCommand::class => [SqlPlusCommand::class];
        yield SqlLoaderCommand::class => [SqlLoaderCommand::class];
    }


    /**
     * @param class-string<OracleCommand> $class
     */
    #[DataProvider('providesCommands')]
    #[BackupGlobals(true)]
    public function testCompiledContainerBuildsTheCommandWithNoCredentialsInTheEnvironment(string $class): void
    {
        unset($_ENV['ORACLE_USER'], $_ENV['ORACLE_PASS'], $_ENV['ORACLE_CONN']);
        unset($_SERVER['ORACLE_USER'], $_SERVER['ORACLE_PASS'], $_SERVER['ORACLE_CONN']);
        $container = self::compiledContainer($class, LogonOptions::class);

        $command = self::service($container, $class);
        $logon = self::service($container, LogonOptions::class);

        self::assertTrue($command->getDefinition()->hasOption('USER'));
        self::assertNull($logon->username);
        self::assertNull($logon->password);
        self::assertNull($logon->connectIdentifier);
    }


    #[BackupGlobals(true)]
    public function testReadsTheCredentialsFromTheEnvironment(): void
    {
        $_ENV['ORACLE_USER'] = 'scott';
        $_ENV['ORACLE_PASS'] = 'tiger';
        $_ENV['ORACLE_CONN'] = 'orcl';
        $container = self::compiledContainer(LogonOptions::class);

        $logon = self::service($container, LogonOptions::class);

        self::assertSame('scott', $logon->username);
        self::assertSame('tiger', $logon->password);
        self::assertSame('orcl', $logon->connectIdentifier);
    }
}
