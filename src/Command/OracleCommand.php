<?php

declare(strict_types=1);

namespace Gadget\Oracle\Command;

use Gadget\Console\Command\ShellCommand;
use Gadget\Oracle\Input\OracleOptions;
use Gadget\Process\ProcessShellEnv;
use Symfony\Component\Console\Input\InputInterface;

abstract class OracleCommand extends ShellCommand
{
    public function __construct(
        protected OracleOptions $oracleOptions,
        ProcessShellEnv $shellEnv,
        bool $throwOnError = true,
        string|null $name = null
    ) {
        parent::__construct(
            shellEnv: $shellEnv,
            throwOnError: $throwOnError,
            name: $name
        );
    }


    /** @inheritdoc */
    protected function configure(): void
    {
        $this->oracleOptions->configureCommand($this);
    }


    /** @inheritdoc */
    protected function getShellArgs(InputInterface $input): array
    {
        return [
            $this->oracleOptions->parseInput($input)->createShellArguments()
        ];
    }
}
