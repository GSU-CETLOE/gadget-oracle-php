<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Command;

use Gadget\Oracle\Command\OracleCommand;
use Gadget\Process\ProcessShellArgs;
use Symfony\Component\Console\Input\InputInterface;

/**
 * A concrete OracleCommand that lets a test read the shell arguments it would run.
 */
final class InspectableOracleCommand extends OracleCommand
{
    /**
     * @param InputInterface $input
     * @return (ProcessShellArgs|string[])[]
     */
    public function shellArgs(InputInterface $input): array
    {
        return $this->getShellArgs($input);
    }
}
