<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Input;

use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\SqlPlusOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

final class SqlPlusOptionsTest extends TestCase
{
    /**
     * A command with the SQL*Plus options registered on it.
     *
     * @param SqlPlusOptions $options
     * @return Command
     */
    private static function configuredCommand(SqlPlusOptions $options): Command
    {
        $command = new Command('test');
        $options->configureCommand($command);
        return $command;
    }


    /**
     * Parses command-line values through a command configured with the options.
     *
     * @param SqlPlusOptions $options
     * @param array<string, string|bool|string[]> $values
     * @return SqlPlusOptions
     */
    private static function parse(SqlPlusOptions $options, array $values): SqlPlusOptions
    {
        return $options->parseInput(new ArrayInput($values, self::configuredCommand($options)->getDefinition()));
    }


    public function testDefaultsToTheSqlPlusExecutableOnThePath(): void
    {
        self::assertSame('sqlplus', (new SqlPlusOptions(new LogonOptions()))->command);
    }


    public function testUsesTheConfiguredExecutable(): void
    {
        $options = new SqlPlusOptions(new LogonOptions(), '/opt/oracle/bin/sqlplus');

        self::assertSame('/opt/oracle/bin/sqlplus', $options->command);
        self::assertSame('/opt/oracle/bin/sqlplus', $options->createShellArguments()[0]);
    }


    public function testConfigureCommandRegistersTheSqlPlusOptionsAndArguments(): void
    {
        $definition = self::configuredCommand(new SqlPlusOptions(new LogonOptions()))->getDefinition();

        foreach (['C', 'M', 'R'] as $name) {
            self::assertTrue($definition->getOption($name)->isValueRequired(), "$name should take a value");
        }
        foreach (['F', 'L', 'NOLOGINTIME', 'S'] as $name) {
            self::assertFalse($definition->getOption($name)->acceptValue(), "$name should be a flag");
        }
        self::assertTrue($definition->hasOption('USER'));

        self::assertTrue($definition->getArgument('script')->isRequired());
        self::assertTrue($definition->getArgument('parameters')->isArray());
        self::assertFalse($definition->getArgument('parameters')->isRequired());
    }


    public function testParseInputReadsEveryOptionAndArgument(): void
    {
        $options = self::parse(new SqlPlusOptions(new LogonOptions()), [
            'script' => 'report.sql',
            'parameters' => ['2026', 'FALL'],
            '--C' => '10.2.0',
            '--F' => true,
            '--L' => true,
            '--M' => 'CSV ON',
            '--NOLOGINTIME' => true,
            '--R' => '3',
            '--S' => true,
            '--USER' => 'scott',
        ]);

        self::assertSame('report.sql 2026 FALL', $options->script);
        self::assertSame('10.2.0', $options->C);
        self::assertTrue($options->F);
        self::assertTrue($options->L);
        self::assertSame('CSV ON', $options->M);
        self::assertTrue($options->NOLOGINTIME);
        self::assertSame(3, $options->R);
        self::assertTrue($options->S);
        self::assertSame('scott', $options->logon->username);
    }


    public function testParseInputLeavesUnsetOptionsAtTheirDefaults(): void
    {
        $options = self::parse(new SqlPlusOptions(new LogonOptions()), ['script' => 'report.sql']);

        self::assertSame('report.sql', $options->script);
        self::assertNull($options->C);
        self::assertFalse($options->F);
        self::assertFalse($options->L);
        self::assertNull($options->M);
        self::assertFalse($options->NOLOGINTIME);
        self::assertNull($options->R);
        self::assertFalse($options->S);
    }


    public function testParseInputReturnsTheOptions(): void
    {
        $options = new SqlPlusOptions(new LogonOptions());
        $input = new ArrayInput(['script' => 'report.sql'], self::configuredCommand($options)->getDefinition());

        self::assertSame($options, $options->parseInput($input));
    }


    public function testCreateShellArgumentsPutsEverythingInSqlPlusOrder(): void
    {
        $options = new SqlPlusOptions(
            new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: 'orcl'),
            script: 'report.sql 2026',
            C: '10.2.0',
            F: true,
            L: true,
            M: 'CSV ON',
            NOLOGINTIME: true,
            R: 3,
            S: true
        );

        self::assertSame(
            [
                'sqlplus',
                '-C=10.2.0',
                '-F',
                '-L',
                '-M "CSV ON"',
                '-NOLOGINTIME',
                '-R 3',
                '-S',
                'scott/tiger@orcl',
                '@report.sql 2026',
            ],
            array_values($options->createShellArguments())
        );
    }


    public function testCreateShellArgumentsLeavesOutOptionsThatAreNotSet(): void
    {
        $options = new SqlPlusOptions(new LogonOptions(username: 'scott', password: 'tiger'), script: 'report.sql');

        self::assertSame(['sqlplus', 'scott/tiger', '@report.sql'], array_values($options->createShellArguments()));
    }


    public function testCreateShellArgumentsKeepsAScriptThatIsAlreadyPrefixed(): void
    {
        $options = new SqlPlusOptions(new LogonOptions(noLog: true), script: '@setup.sql');

        self::assertSame(['sqlplus', '/NOLOG', '@setup.sql'], array_values($options->createShellArguments()));
    }


    public function testCreateShellArgumentsWithNoScriptAndNoCredentials(): void
    {
        $options = new SqlPlusOptions(new LogonOptions());

        self::assertSame(['sqlplus', '/'], array_values($options->createShellArguments()));
    }


    public function testCreateShellArgumentsAppendsExtraArguments(): void
    {
        $options = new SqlPlusOptions(new LogonOptions());

        self::assertSame(['sqlplus', '/', '--extra'], array_values($options->createShellArguments(['--extra', null])));
    }
}
