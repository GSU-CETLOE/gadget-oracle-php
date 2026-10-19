<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Command;

use Gadget\Console\Command\Command;
use Gadget\Console\Command\ShellCommand;
use Gadget\Log\LoggerProxyInterface;
use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\OracleOptions;
use Gadget\Oracle\Input\SqlPlusOptions;
use Gadget\Process\ProcessShellArgs;
use Gadget\Process\ProcessShellEnv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

final class OracleCommandTest extends TestCase
{
    /**
     * @param OracleOptions $options
     * @return InspectableOracleCommand
     */
    private static function command(OracleOptions $options): InspectableOracleCommand
    {
        return new InspectableOracleCommand($options, new ProcessShellEnv(), true, 'oracle:test');
    }


    public function testIsAGadgetShellCommand(): void
    {
        $command = self::command(new SqlPlusOptions(new LogonOptions()));

        self::assertInstanceOf(ShellCommand::class, $command);
        self::assertInstanceOf(Command::class, $command);
        self::assertInstanceOf(LoggerProxyInterface::class, $command);
        self::assertSame('oracle:test', $command->getName());
    }


    public function testConfigureRegistersTheOptionsOnTheCommand(): void
    {
        $definition = self::command(new SqlPlusOptions(new LogonOptions()))->getDefinition();

        self::assertTrue($definition->hasOption('USER'));
        self::assertTrue($definition->hasOption('S'));
        self::assertTrue($definition->hasArgument('script'));
    }


    public function testGetShellArgsReturnsOneCommandLineBuiltFromTheInput(): void
    {
        $command = self::command(new SqlPlusOptions(new LogonOptions()));
        $input = new ArrayInput(
            ['script' => 'report.sql', '--S' => true, '--USER' => 'scott', '--PASS' => 'tiger'],
            $command->getDefinition()
        );

        self::assertSame(
            [['sqlplus', '-S', 'scott/tiger', '@report.sql']],
            array_map(
                fn(ProcessShellArgs|array $args): array => is_array($args) ? array_values($args) : $args->getArgs(),
                $command->shellArgs($input)
            )
        );
    }
}
