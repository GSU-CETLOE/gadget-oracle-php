<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Input;

use Gadget\Oracle\Input\LogonOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

final class LogonOptionsTest extends TestCase
{
    /**
     * A command with the logon options registered on it.
     *
     * @param LogonOptions $options
     * @return Command
     */
    private static function configuredCommand(LogonOptions $options): Command
    {
        $command = new Command('test');
        $options->configureCommand($command);
        return $command;
    }


    /**
     * Parses command-line values through a command configured with the options.
     *
     * @param LogonOptions $options
     * @param array<string, string|bool> $values
     * @return LogonOptions
     */
    private static function parse(LogonOptions $options, array $values): LogonOptions
    {
        return $options->parseInput(new ArrayInput($values, self::configuredCommand($options)->getDefinition()));
    }


    public function testConfigureCommandRegistersTheLogonOptions(): void
    {
        $definition = self::configuredCommand(new LogonOptions())->getDefinition();

        foreach (['USER', 'PASS', 'CONN', 'SYS', 'ED'] as $name) {
            self::assertTrue($definition->hasOption($name), "$name should be registered");
            self::assertTrue($definition->getOption($name)->isValueRequired(), "$name should take a value");
        }

        self::assertTrue($definition->hasOption('NOLOG'));
        self::assertFalse($definition->getOption('NOLOG')->acceptValue());
    }


    public function testConfigureCommandReturnsTheOptions(): void
    {
        $options = new LogonOptions();

        self::assertSame($options, $options->configureCommand(new Command('test')));
    }


    public function testParseInputReadsEveryOption(): void
    {
        $options = self::parse(new LogonOptions(), [
            '--USER' => 'scott',
            '--PASS' => 'tiger',
            '--CONN' => 'orcl',
            '--SYS' => 'SYSDBA',
            '--ED' => 'ora$base',
            '--NOLOG' => true,
        ]);

        self::assertSame('scott', $options->username);
        self::assertSame('tiger', $options->password);
        self::assertSame('orcl', $options->connectIdentifier);
        self::assertSame('SYSDBA', $options->asSys);
        self::assertSame('ora$base', $options->edition);
        self::assertTrue($options->noLog);
    }


    public function testParseInputKeepsConstructorValuesForOptionsNotGiven(): void
    {
        $options = self::parse(
            new LogonOptions(
                username: 'scott',
                password: 'tiger',
                connectIdentifier: 'orcl',
                asSys: 'SYSDBA',
                edition: 'ora$base'
            ),
            []
        );

        self::assertSame('scott', $options->username);
        self::assertSame('tiger', $options->password);
        self::assertSame('orcl', $options->connectIdentifier);
        self::assertSame('SYSDBA', $options->asSys);
        self::assertSame('ora$base', $options->edition);
    }


    /**
     * NOLOG is a flag, so Symfony reports it as false whenever it is missing
     * from the command line. That false replaces the constructor value.
     */
    public function testParseInputTurnsNoLogOffWhenTheFlagIsAbsent(): void
    {
        self::assertFalse(self::parse(new LogonOptions(noLog: true), [])->noLog);
    }


    public function testParseInputReturnsTheOptions(): void
    {
        $options = new LogonOptions();
        $input = new ArrayInput([], self::configuredCommand($options)->getDefinition());

        self::assertSame($options, $options->parseInput($input));
    }


    /**
     * @return \Generator<string, array{LogonOptions, string|null}>
     */
    public static function providesLogonStrings(): \Generator
    {
        yield 'nothing set' => [new LogonOptions(), '/'];
        yield 'username and password' => [
            new LogonOptions(username: 'scott', password: 'tiger'),
            'scott/tiger'
        ];
        yield 'connect identifier' => [
            new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: 'orcl'),
            'scott/tiger@orcl'
        ];
        yield 'connect identifier already prefixed' => [
            new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: '@//db.example.edu:1521/orcl'),
            'scott/tiger@//db.example.edu:1521/orcl'
        ];
        yield 'as sys' => [
            new LogonOptions(username: 'sys', password: 'secret', connectIdentifier: 'orcl', asSys: 'SYSDBA'),
            'sys/secret@orcl AS SYSDBA'
        ];
        yield 'as sys already prefixed' => [
            new LogonOptions(username: 'sys', password: 'secret', connectIdentifier: 'orcl', asSys: 'AS SYSOPER'),
            'sys/secret@orcl AS SYSOPER'
        ];
        yield 'edition' => [
            new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: 'orcl', edition: 'ora$base'),
            'scott/tiger@orcl EDITION=ora$base'
        ];
        yield 'edition already prefixed' => [
            new LogonOptions(
                username: 'scott',
                password: 'tiger',
                connectIdentifier: 'orcl',
                edition: 'EDITION=release2'
            ),
            'scott/tiger@orcl EDITION=release2'
        ];
        yield 'everything' => [
            new LogonOptions(
                username: 'sys',
                password: 'secret',
                connectIdentifier: 'orcl',
                asSys: 'SYSDBA',
                edition: 'ora$base'
            ),
            'sys/secret@orcl AS SYSDBA EDITION=ora$base'
        ];
        yield 'nolog wins over credentials' => [
            new LogonOptions(noLog: true, username: 'scott', password: 'tiger'),
            null
        ];
    }


    #[DataProvider('providesLogonStrings')]
    public function testCreateShellArgumentsBuildsTheLogonString(LogonOptions $options, string|null $expected): void
    {
        self::assertSame($expected, $options->createShellArguments());
    }
}
