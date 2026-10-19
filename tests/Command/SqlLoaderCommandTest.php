<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Command;

use Gadget\Oracle\Command\OracleCommand;
use Gadget\Oracle\Command\SqlLoaderCommand;
use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\SqlLoaderOptions;
use Gadget\Process\ProcessException;
use Gadget\Process\ProcessShellEnv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Runs the command against a stand-in executable that echoes its arguments.
 */
final class SqlLoaderCommandTest extends TestCase
{
    private const SQLLDR = __DIR__ . '/../Fixtures/echo-args';


    /**
     * @param bool $throwOnError
     * @param array<string, string> $env
     * @return SqlLoaderCommand
     */
    private static function command(bool $throwOnError = true, array $env = []): SqlLoaderCommand
    {
        return new SqlLoaderCommand(
            new SqlLoaderOptions(new LogonOptions(), self::SQLLDR),
            new ProcessShellEnv(__DIR__, $env),
            $throwOnError
        );
    }


    /**
     * The non-empty lines the command wrote to the console.
     *
     * @param CommandTester $tester
     * @return string[]
     */
    private static function outputLines(CommandTester $tester): array
    {
        return array_values(array_filter(
            array_map(trim(...), explode("\n", $tester->getDisplay())),
            fn(string $line): bool => $line !== ''
        ));
    }


    public function testIsNamedAndDescribedByItsAttribute(): void
    {
        $command = self::command();

        self::assertInstanceOf(OracleCommand::class, $command);
        self::assertSame('oracle:sqlldr', $command->getName());
        self::assertSame(
            'SQL*Loader loads data from external files into Oracle Database tables.',
            $command->getDescription()
        );
    }


    public function testRunsSqlLoaderWithTheParsedOptions(): void
    {
        $tester = new CommandTester(self::command());

        $exitCode = $tester->execute([
            '--control' => 'load.ctl',
            '--skip' => '10',
            '--direct' => 'true',
            '--USER' => 'scott',
            '--PASS' => 'tiger',
            '--CONN' => 'orcl',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(
            ['userid=scott/tiger@orcl', 'control=load.ctl', 'skip=10', 'direct=true'],
            self::outputLines($tester)
        );
    }


    public function testThrowsWhenSqlLoaderExitsWithAnError(): void
    {
        $tester = new CommandTester(self::command(env: ['ECHO_ARGS_EXIT_CODE' => '2']));

        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('Invalid exit code: 2');

        $tester->execute(['--control' => 'load.ctl']);
    }


    public function testReportsSuccessOnAnErrorWhenToldNotToThrow(): void
    {
        $tester = new CommandTester(self::command(throwOnError: false, env: ['ECHO_ARGS_EXIT_CODE' => '2']));

        $exitCode = $tester->execute(['--control' => 'load.ctl']);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(['userid=/', 'control=load.ctl'], self::outputLines($tester));
    }
}
