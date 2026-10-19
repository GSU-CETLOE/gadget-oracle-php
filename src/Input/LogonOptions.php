<?php

declare(strict_types=1);

namespace Gadget\Oracle\Input;

use Gadget\Io\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Specifies the database account username, password and connect identifier for the database connection.
 */
final class LogonOptions
{
    /**
     * @param bool $noLog
     * @param string|null $username
     * @param string|null $password
     * @param string|null $connectIdentifier
     * @param string|null $asSys
     * @param string|null $edition
     */
    public function __construct(
        public bool $noLog = false,
        public string|null $username = null,
        public string|null $password = null,
        public string|null $connectIdentifier = null,
        public string|null $asSys = null,
        public string|null $edition = null,
    ) {
    }


    /**
     * @param Command $command
     * @return static
     */
    public function configureCommand(Command $command): static
    {
        $command
            ->addOption(
                name: 'USER',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Specifies the database account username'
            )
            ->addOption(
                name: 'PASS',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Specifies the database account password'
            )
            ->addOption(
                name: 'CONN',
                mode: InputOption::VALUE_REQUIRED,
                description: implode("\n", [
                    'Specifies the database connect identifier. This can be in',
                    'the form of Net Service Name or Easy Connect.'
                ])
            )
            ->addOption(
                name: 'SYS',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Database administration privileges'
            )
            ->addOption(
                name: 'ED',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Specifies the value for Session Edition.'
            )
            ->addOption(
                name: 'NOLOG',
                mode: InputOption::VALUE_NONE,
                description: "Starts SQL*Plus without connecting to a database."
            );

        return $this;
    }


    /**
     * @param InputInterface $input
     * @return static
     */
    public function parseInput(InputInterface $input): static
    {
        $options = $input->getOptions();
        $this->noLog = Cast::toBool($options['NOLOG'] ?? $this->noLog);
        $this->username = Cast::toValueOrNull($options['USER'] ?? $this->username, Cast::toString(...));
        $this->password = Cast::toValueOrNull($options['PASS'] ?? $this->password, Cast::toString(...));
        $this->connectIdentifier = Cast::toValueOrNull(
            $options['CONN'] ?? $this->connectIdentifier,
            Cast::toString(...)
        );
        $this->asSys = Cast::toValueOrNull($options['SYS'] ?? $this->asSys, Cast::toString(...));
        $this->edition = Cast::toValueOrNull($options['ED'] ?? $this->edition, Cast::toString(...));

        return $this;
    }


    /**
     * @return string|null
     */
    public function createShellArguments(): string|null
    {
        if ($this->noLog === true) {
            return null;
        }

        $logon = sprintf(
            "%s/%s%s",
            $this->username ?? "",
            $this->password ?? "",
            is_string($this->connectIdentifier)
                ? (str_starts_with($this->connectIdentifier, "@") ? "" : "@") . $this->connectIdentifier
                : ""
        );

        if (is_string($this->asSys)) {
            $logon .= " " . (str_starts_with($this->asSys, "AS ") ? "" : "AS ") . $this->asSys;
        }

        if (is_string($this->edition)) {
            $logon .= " " . (str_starts_with($this->edition, "EDITION=") ? "" : "EDITION=") . $this->edition;
        }

        return $logon;
    }
}
