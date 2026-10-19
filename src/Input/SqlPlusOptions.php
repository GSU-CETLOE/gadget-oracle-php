<?php

declare(strict_types=1);

namespace Gadget\Oracle\Input;

use Gadget\Io\Cast;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class SqlPlusOptions extends OracleOptions
{
    /**
     * @param LogonOptions $logon ORACLE username/password
     * @param string|null $sqlPlus Path to SQL*Plus
     * @param string|null $script Runs the specified SQL*Plus script from a web server (URL) or the local file system
     * (filename.ext) with specified parameters that will be assigned to substitution variables in the script. Must be
     * in the form `@<URL>|<filename>[.<ext>] [<parameter> ...]`
     * @param string|null $C Sets the compatibility of affected commands to the specified version. Must be in the form
     * `x.y[.z]`
     * @param bool $F This option improves performance in general. It changes the default values settings.
     * @param bool $L Attempts to log on just once, instead of reprompting on error.
     * @param string|null $M Sets automatic HTML or CSV markup of output. Must be in the form:
     * `{HTML html_options|CSV csv_options}`
     * @param bool $NOLOGINTIME Don't display Last Successful Login Time.
     * @param int|null $R Sets restricted mode to disable SQL*Plus commands that interact with the file system. The
     * level can be `1`, `2` or `3`. The most restrictive is `3` which disables all user commands interacting with the
     * file system.
     * @param bool $S Sets silent mode which suppresses the display of the SQL*Plus banner, prompts, and echoing of
     * commands.
     */
    public function __construct(
        LogonOptions $logon,
        string|null $sqlPlus = 'sqlplus',
        public string|null $script = null,
        public string|null $C = null,
        public bool $F = false,
        public bool $L = false,
        public string|null $M = null,
        public bool $NOLOGINTIME = false,
        public int|null $R = null,
        public bool $S = false
    ) {
        parent::__construct(
            logon: $logon,
            command: $sqlPlus,
            optionsMap: [
                'C' => implode(" ", [
                    "Sets the compatibility of affected commands to the specified version. The version has form",
                    "\"x.y[.z]\". For example, -C=10.2.0"
                ]),
                'F' => [
                    implode(" ", [
                        "This option improves performance in general. It changes the default values settings. See",
                        "SQL*Plus User's Guide for the detailed settings."
                    ]),
                    InputOption::VALUE_NONE
                ],
                'L' => [
                    "Attempts to log on just once, instead of reprompting on error.",
                    InputOption::VALUE_NONE
                ],
                'M' => implode(" ", [
                    "Sets automatic HTML or CSV markup of output. The options have the form:",
                    "{HTML html_options|CSV csv_options}",
                    "See SQL*Plus User's Guide for detailed HTML and CSV options."
                ]),
                'NOLOGINTIME' => [
                    "Don't display Last Successful Login Time.",
                    InputOption::VALUE_NONE
                ],
                'R' => implode(" ", [
                    "Sets restricted mode to disable SQL*Plus commands that interact with the file system. The level",
                    "can be 1, 2 or 3. The most restrictive is --R=3 which disables all user commands interacting with",
                    "the file system."
                ]),
                'S' => [
                    implode(" ", [
                        "Sets silent mode which suppresses the display of the SQL*Plus banner, prompts, and echoing ",
                        "of commands."
                    ]),
                    InputOption::VALUE_NONE
                ],
            ],
            argumentsMap: [
                'script' => implode(" ", [
                    "Runs the specified SQL*Plus script from a web server (URL) or the local file system (filename.ext)"
                ]),
                'parameters' => [
                    "Specified parameters that will be assigned to substitution variables in the script",
                    InputArgument::IS_ARRAY
                ]
            ]
        );
    }


    /** @inheritdoc */
    public function parseInput(InputInterface $input): static
    {
        $options = $input->getOptions();

        /** @var string $script */
        $script = $input->getArgument('script');
        /** @var string[] $params */
        $params = $input->getArgument('parameters');

        $this->script = implode(" ", [$script, ...$params]);
        $this->C = Cast::toValueOrNull($options['C'] ?? $this->C, Cast::toString(...));
        $this->F = ($options['F'] ?? $this->F) === true;
        $this->L = ($options['L'] ?? $this->L) === true;
        $this->M = Cast::toValueOrNull($options['M'] ?? $this->M, Cast::toString(...));
        $this->NOLOGINTIME = ($options['NOLOGINTIME'] ?? $this->NOLOGINTIME) === true;
        $this->R = Cast::toValueOrNull($options['R'] ?? $this->R, Cast::toInt(...));
        $this->S = ($options['S'] ?? $this->S) === true;

        return parent::parseInput($input);
    }


    /** @inheritdoc */
    public function createShellArguments(array $shellArgs = []): array
    {
        return parent::createShellArguments([
            is_string($this->C) ? "-C={$this->C}" : null,
            $this->F ? "-F" : null,
            $this->L ? "-L" : null,
            is_string($this->M) ? "-M \"{$this->M}\"" : null,
            $this->NOLOGINTIME ? "-NOLOGINTIME" : null,
            is_int($this->R) ? "-R {$this->R}" : null,
            $this->S ? "-S" : null,
            strval($this->logon->createShellArguments() ?? '/NOLOG'),
            is_string($this->script) && !str_starts_with($this->script, '@')
                ? "@{$this->script}"
                : $this->script,
            ...$shellArgs
        ]);
    }
}
