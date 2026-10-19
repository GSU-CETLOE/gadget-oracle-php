<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Command;

use Gadget\Oracle\Command\OracleCommand;
use Gadget\Oracle\Command\SqlPlusCommand;
use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\SqlPlusOptions;
use Gadget\Process\ProcessException;
use Gadget\Process\ProcessShellEnv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Runs the command against a stand-in executable that echoes its arguments.
 */
final class SqlPlusCommandTest extends TestCase
{
    private const SQLPLUS = __DIR__ . '/../Fixtures/echo-args';


    /**
     * @param bool $throwOnError
     * @param array<string, string> $env
     * @return SqlPlusCommand
     */
    private static function command(bool $throwOnError = true, array $env = []): SqlPlusCommand
    {
        return new SqlPlusCommand(
            new SqlPlusOptions(new LogonOptions(), self::SQLPLUS),
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
        self::assertSame('oracle:sqlplus', $command->getName());
        self::assertSame('SQL*Plus executes SQL, PL/SQL and SQL*Plus statements.', $command->getDescription());
    }


    public function testRunsSqlPlusWithTheLogonAndScriptArguments(): void
    {
        $tester = new CommandTester(self::command());

        $exitCode = $tester->execute([
            'script' => 'report.sql',
            'parameters' => ['2026', 'FALL'],
            '--S' => true,
            '--USER' => 'scott',
            '--PASS' => 'tiger',
            '--CONN' => 'orcl',
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(['-S', 'scott/tiger@orcl', '@report.sql 2026 FALL'], self::outputLines($tester));
    }


    public function testRunsWithoutCredentialsWhenNoLogIsGiven(): void
    {
        $tester = new CommandTester(self::command());

        $exitCode = $tester->execute(['script' => 'setup.sql', '--NOLOG' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(['/NOLOG', '@setup.sql'], self::outputLines($tester));
    }


    public function testThrowsWhenSqlPlusExitsWithAnError(): void
    {
        $tester = new CommandTester(self::command(env: ['ECHO_ARGS_EXIT_CODE' => '3']));

        $this->expectException(ProcessException::class);
        $this->expectExceptionMessage('Invalid exit code: 3');

        $tester->execute(['script' => 'bad.sql']);
    }


    public function testReportsSuccessOnAnErrorWhenToldNotToThrow(): void
    {
        $tester = new CommandTester(self::command(throwOnError: false, env: ['ECHO_ARGS_EXIT_CODE' => '3']));

        $exitCode = $tester->execute(['script' => 'bad.sql']);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(['/', '@bad.sql'], self::outputLines($tester));
    }
}
