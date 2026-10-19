<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Input;

use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\OracleOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class OracleOptionsTest extends TestCase
{
    /**
     * A concrete OracleOptions built from the given maps.
     *
     * @param string|null $command
     * @param array<string, string|array{string, int}> $optionsMap
     * @param array<string, string|array{string, int}> $argumentsMap
     * @return OracleOptions
     */
    private static function options(
        string|null $command = 'tool',
        array $optionsMap = [],
        array $argumentsMap = []
    ): OracleOptions {
        return new class (new LogonOptions(), $command, $optionsMap, $argumentsMap) extends OracleOptions {
        };
    }


    /**
     * A command with the options registered on it.
     *
     * @param OracleOptions $options
     * @return Command
     */
    private static function configuredCommand(OracleOptions $options): Command
    {
        $command = new Command('test');
        $options->configureCommand($command);
        return $command;
    }


    public function testConfigureCommandRegistersTheLogonOptions(): void
    {
        $definition = self::configuredCommand(self::options())->getDefinition();

        self::assertTrue($definition->hasOption('USER'));
        self::assertTrue($definition->hasOption('NOLOG'));
    }


    public function testConfigureCommandRegistersOptionsFromTheMap(): void
    {
        $definition = self::configuredCommand(self::options(optionsMap: [
            'level' => 'Output level',
            'quiet' => ['Suppress output', InputOption::VALUE_NONE],
        ]))->getDefinition();

        $level = $definition->getOption('level');
        self::assertSame('Output level', $level->getDescription());
        self::assertTrue($level->isValueRequired());

        $quiet = $definition->getOption('quiet');
        self::assertSame('Suppress output', $quiet->getDescription());
        self::assertFalse($quiet->acceptValue());
    }


    public function testConfigureCommandRegistersArgumentsFromTheMap(): void
    {
        $definition = self::configuredCommand(self::options(argumentsMap: [
            'file' => 'Input file',
            'extras' => ['More files', InputArgument::IS_ARRAY],
        ]))->getDefinition();

        $file = $definition->getArgument('file');
        self::assertSame('Input file', $file->getDescription());
        self::assertTrue($file->isRequired());

        $extras = $definition->getArgument('extras');
        self::assertSame('More files', $extras->getDescription());
        self::assertTrue($extras->isArray());
        self::assertFalse($extras->isRequired());
    }


    public function testConfigureCommandReturnsTheOptions(): void
    {
        $options = self::options();

        self::assertSame($options, $options->configureCommand(new Command('test')));
    }


    public function testParseInputReadsTheLogonOptions(): void
    {
        $options = self::options();
        $input = new ArrayInput(['--USER' => 'scott'], self::configuredCommand($options)->getDefinition());

        self::assertSame($options, $options->parseInput($input));
        self::assertSame('scott', $options->logon->username);
    }


    public function testCreateShellArgumentsStartsWithTheCommandAndDropsNulls(): void
    {
        self::assertSame(
            ['tool', '-a', 'b'],
            array_values(self::options()->createShellArguments(['-a', null, 'b']))
        );
    }


    public function testCreateShellArgumentsUsesAnEmptyStringWhenThereIsNoCommand(): void
    {
        self::assertSame([''], self::options(command: null)->createShellArguments());
    }
}
