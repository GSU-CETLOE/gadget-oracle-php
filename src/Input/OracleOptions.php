<?php

declare(strict_types=1);

namespace Gadget\Oracle\Input;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class OracleOptions
{
    /**
     * @param LogonOptions $logon ORACLE username/password
     * @param string|null $command Path to ORACLE command
     * @param array<string,string|array{string,int}> $optionsMap
     * @param array<string,string|array{string,int}> $argumentsMap
     */
    public function __construct(
        public LogonOptions $logon,
        public string|null $command = null,
        protected array $optionsMap = [],
        protected array $argumentsMap = []
    ) {
    }


    /**
     * @param Command $command
     * @return static
     */
    public function configureCommand(Command $command): static
    {
        $this->logon->configureCommand($command);

        foreach ($this->optionsMap as $name => $description) {
            list($description, $mode) = is_array($description)
                ? $description
                : [$description, InputOption::VALUE_REQUIRED];

            $command->addOption(
                name: $name,
                mode: $mode,
                description: $description
            );
        }

        foreach ($this->argumentsMap as $name => $description) {
            list($description, $mode) = is_array($description)
                ? $description
                : [$description, InputArgument::REQUIRED];

            $command->addArgument(
                name: $name,
                mode: $mode,
                description: $description
            );
        }

        return $this;
    }


    /**
     * @param InputInterface $input
     * @return static
     */
    public function parseInput(InputInterface $input): static
    {
        $this->logon->parseInput($input);
        return $this;
    }


    /**
     * @param (string|null)[] $shellArgs
     * @return string[]
     */
    public function createShellArguments(array $shellArgs = []): array
    {
        return array_filter([
            $this->command ?? '',
            ...array_values($shellArgs)
        ], fn($v) => $v !== null);
    }
}
