<?php

declare(strict_types=1);

namespace Gadget\Oracle\Input;

use Gadget\Io\Cast;
use Symfony\Component\Console\Input\InputInterface;

final class SqlLoaderOptions extends OracleOptions
{
    /**
     * @param LogonOptions $logon ORACLE username/password
     * @param string|null $sqlldr Path to SQL*Loader
     * @param string|null $control Control file name
     * @param string|null $log Log file name
     * @param string|null $bad Bad file name
     * @param string|null $data Data file name
     * @param string|null $discard Discard file name
     * @param int|null $discardmax Number of discards to allow
     * @param int|null $skip Number of logical records to skip
     * @param int|null $load Number of logical records to load
     * @param int|null $errors Number of errors to allow
     * @param int|null $rows Number of rows in conventional path bind array or between direct path data saves
     * @param int|null $bindsize Size of conventional path bind array in bytes
     * @param string|null $silent Suppress messages during run (header,feedback,errors,discards,partitions)
     * @param bool|null $direct Use direct path
     * @param string|null $parfile Parameter file: name of file that contains parameter specifications
     * @param bool|null $parallel Do parallel load
     * @param string|null $file File to allocate extents from
     * @param bool|null $skipUnusableIndexes Disallow/allow unusable indexes or index partitions
     * @param bool|null $skipIndexMaintenance Do not maintain indexes, mark affected indexes as unusable
     * @param bool|null $commitDiscontinued Commit loaded rows when load is discontinued
     * @param int|null $readsize Size of read buffer
     * @param string|null $externalTable Use external table for load; `NOT_USED`, `GENERATE_ONLY`, `EXECUTE`
     * @param int|null $columnarrayrows Number of rows for direct path column array
     * @param int|null $streamsize Size of direct path stream buffer in bytes
     * @param bool|null $multithreading Use multithreading in direct path
     * @param bool|null $resumable Enable or disable resumable for current session
     * @param string|null $resumableName Text string to help identify resumable statement
     * @param int|null $resumableTimeout Wait time (in seconds) for `RESUMABLE`
     * @param int|null $dateCache Size (in entries) of date conversion cache
     * @param bool|null $noIndexErrors Abort load on any index errors
     * @param int|null $partitionMemory Direct path partition memory limit to start spilling (kb)
     * @param string|null $table Table for express mode load
     * @param string|null $dateFormat Date format for express mode load
     * @param string|null $timestampFormat Timestamp format for express mode load
     * @param string|null $terminatedBy Terminated by character for express mode load
     * @param string|null $enclosedBy Enclosed by character for express mode load
     * @param string|null $optionallyEnclosedBy Optionally enclosed by character for express mode load
     * @param string|null $characterset Characterset for express mode load
     * @param string|null $degreeOfParallelism Degree of parallelism for express mode load and external table load
     * @param string|null $trim Trim type for express mode load and external table load
     * @param string|null $csv CSV format data files for express mode load
     * @param string|null $nullif Table level `nullif` clause for express mode load
     * @param string|null $fieldNames Field names setting for first record of data files for express mode load
     * @param bool|null $dnfsEnable Option for enabling or disabling Direct NFS (dNFS) for input data files
     * @param int|null $dnfsReadbuffers Number of Direct NFS (dNFS) read buffers
     * @param string|null $sdfPrefix Prefix to append to start of every `LOB` File and Secondary Data File
     * @param bool|null $displayHelp Display help messages
     * @param bool|null $emptyLobsAreNull Set empty LOBs to `null`
     * @param string|null $defaults Direct path default value loading; `EVALUATE_ONCE`, `EVALUATE_EVERY_ROW`, `IGNORE`,
     * `IGNORE_UNSUPPORTED_EVALUATE_ONCE`, `IGNORE_UNSUPPORTED_EVALUATE_EVERY_ROW`
     * @param bool|null $directPathLockWait Wait for access to table when currently locked
     * @param string|null $credential Credential used for object store access
     * @param string|null $proxy Proxy used for object store access
     */
    public function __construct(
        LogonOptions $logon,
        string|null $sqlldr = 'sqlldr',
        public string|null $control = null,
        public string|null $log = null,
        public string|null $bad = null,
        public string|null $data = null,
        public string|null $discard = null,
        public int|null $discardmax = null,
        public int|null $skip = null,
        public int|null $load = null,
        public int|null $errors = null,
        public int|null $rows = null,
        public int|null $bindsize = null,
        public string|null $silent = null,
        public bool|null $direct = null,
        public string|null $parfile = null,
        public bool|null $parallel = null,
        public string|null $file = null,
        public bool|null $skipUnusableIndexes = null,
        public bool|null $skipIndexMaintenance = null,
        public bool|null $commitDiscontinued = null,
        public int|null $readsize = null,
        public string|null $externalTable = null,
        public int|null $columnarrayrows = null,
        public int|null $streamsize = null,
        public bool|null $multithreading = null,
        public bool|null $resumable = null,
        public string|null $resumableName = null,
        public int|null $resumableTimeout = null,
        public int|null $dateCache = null,
        public bool|null $noIndexErrors = null,
        public int|null $partitionMemory = null,
        public string|null $table = null,
        public string|null $dateFormat = null,
        public string|null $timestampFormat = null,
        public string|null $terminatedBy = null,
        public string|null $enclosedBy = null,
        public string|null $optionallyEnclosedBy = null,
        public string|null $characterset = null,
        public string|null $degreeOfParallelism = null,
        public string|null $trim = null,
        public string|null $csv = null,
        public string|null $nullif = null,
        public string|null $fieldNames = null,
        public bool|null $dnfsEnable = null,
        public int|null $dnfsReadbuffers = null,
        public string|null $sdfPrefix = null,
        public bool|null $displayHelp = null,
        public bool|null $emptyLobsAreNull = null,
        public string|null $defaults = null,
        public bool|null $directPathLockWait = null,
        public string|null $credential = null,
        public string|null $proxy = null,
    ) {
        parent::__construct(
            logon: $logon,
            command: $sqlldr,
            optionsMap: [
                'userid' => 'ORACLE username/password',
                'control' => 'Control file name',
                'log' => 'Log file name',
                'bad' => 'Bad file name',
                'data' => 'Data file name',
                'discard' => 'Discard file name',
                'discardmax' => 'Number of discards to allow',
                'skip' => 'Number of logical records to skip',
                'load' => 'Number of logical records to load',
                'errors' => 'Number of errors to allow',
                'rows' => 'Number of rows in conventional path bind array or between direct path data saves',
                'bindsize' => 'Size of conventional path bind array in bytes',
                'silent' => 'Suppress messages during run (header,feedback,errors,discards,partitions)',
                'direct' => 'Use direct path',
                'parfile' => 'Parameter file: name of file that contains parameter specifications',
                'parallel' => 'Do parallel load',
                'file' => 'File to allocate extents from',
                'skipUnusableIndexes' => 'Disallow/allow unusable indexes or index partitions',
                'skipIndexMaintenance' => 'Do not maintain indexes, mark affected indexes as unusable',
                'commitDiscontinued' => 'Commit loaded rows when load is discontinued',
                'readsize' => 'Size of read buffer',
                'externalTable' => 'Use external table for load; NOT_USED, GENERATE_ONLY, EXECUTE',
                'columnarrayrows' => 'Number of rows for direct path column array',
                'streamsize' => 'Size of direct path stream buffer in bytes',
                'multithreading' => 'Use multithreading in direct path',
                'resumable' => 'Enable or disable resumable for current session',
                'resumableName' => 'Text string to help identify resumable statement',
                'resumableTimeout' => 'Wait time (in seconds) for RESUMABLE',
                'dateCache' => 'Size (in entries) of date conversion cache',
                'noIndexErrors' => 'Abort load on any index errors',
                'partitionMemory' => 'Direct path partition memory limit to start spilling (kb)',
                'table' => 'Table for express mode load',
                'dateFormat' => 'Date format for express mode load',
                'timestampFormat' => 'Timestamp format for express mode load',
                'terminatedBy' => 'Terminated by character for express mode load',
                'enclosedBy' => 'Enclosed by character for express mode load',
                'optionallyEnclosedBy' => 'Optionally enclosed by character for express mode load',
                'characterset' => 'Characterset for express mode load',
                'degreeOfParallelism' => 'Degree of parallelism for express mode load and external table load',
                'trim' => 'Trim type for express mode load and external table load',
                'csv' => 'CSV format data files for express mode load',
                'nullif' => 'Table level nullif clause for express mode load',
                'fieldNames' => 'Field names setting for first record of data files for express mode load',
                'dnfsEnable' => 'Option for enabling or disabling Direct NFS (dNFS) for input data files',
                'dnfsReadbuffers' => 'Number of Direct NFS (dNFS) read buffers',
                'sdfPrefix' => 'Prefix to append to start of every LOB File and Secondary Data File',
                'displayHelp' => 'Display help messages',
                'emptyLobsAreNull' => 'Set empty LOBs to null',
                'defaults' => implode(" ", [
                    'Direct path default value loading; EVALUATE_ONCE, EVALUATE_EVERY_ROW, IGNORE,',
                    'IGNORE_UNSUPPORTED_EVALUATE_ONCE, IGNORE_UNSUPPORTED_EVALUATE_EVERY_ROW'
                ]),
                'directPathLockWait' => 'Wait for access to table when currently locked',
                'credential' => 'Credential used for object store access',
                'proxy' => 'Proxy used for object store access',
            ]
        );
    }


    /**
     * @param InputInterface $input
     * @return static
     */
    public function parseInput(InputInterface $input): static
    {
        parent::parseInput($input);

        /** @var (mixed[]|bool|float|int|string|null)[] $options */
        $options = $input->getOptions();
        $this->control = Cast::toValueOrNull($options['control'] ?? null, Cast::toString(...));
        $this->log = Cast::toValueOrNull($options['log'] ?? null, Cast::toString(...));
        $this->bad = Cast::toValueOrNull($options['bad'] ?? null, Cast::toString(...));
        $this->data = Cast::toValueOrNull($options['data'] ?? null, Cast::toString(...));
        $this->discard = Cast::toValueOrNull($options['discard'] ?? null, Cast::toString(...));
        $this->discardmax = Cast::toValueOrNull($options['discardmax'] ?? null, Cast::toInt(...));
        $this->skip = Cast::toValueOrNull($options['skip'] ?? null, Cast::toInt(...));
        $this->load = Cast::toValueOrNull($options['load'] ?? null, Cast::toInt(...));
        $this->errors = Cast::toValueOrNull($options['errors'] ?? null, Cast::toInt(...));
        $this->rows = Cast::toValueOrNull($options['rows'] ?? null, Cast::toInt(...));
        $this->bindsize = Cast::toValueOrNull($options['bindsize'] ?? null, Cast::toInt(...));
        $this->silent = Cast::toValueOrNull($options['silent'] ?? null, Cast::toString(...));
        $this->direct = Cast::toValueOrNull($options['direct'] ?? null, Cast::toBool(...));
        $this->parfile = Cast::toValueOrNull($options['parfile'] ?? null, Cast::toString(...));
        $this->parallel = Cast::toValueOrNull($options['parallel'] ?? null, Cast::toBool(...));
        $this->file = Cast::toValueOrNull($options['file'] ?? null, Cast::toString(...));
        $this->skipUnusableIndexes = Cast::toValueOrNull($options['skipUnusableIndexes'] ?? null, Cast::toBool(...));
        $this->skipIndexMaintenance = Cast::toValueOrNull($options['skipIndexMaintenance'] ?? null, Cast::toBool(...));
        $this->commitDiscontinued = Cast::toValueOrNull($options['commitDiscontinued'] ?? null, Cast::toBool(...));
        $this->readsize = Cast::toValueOrNull($options['readsize'] ?? null, Cast::toInt(...));
        $this->externalTable = Cast::toValueOrNull($options['externalTable'] ?? null, Cast::toString(...));
        $this->columnarrayrows = Cast::toValueOrNull($options['columnarrayrows'] ?? null, Cast::toInt(...));
        $this->streamsize = Cast::toValueOrNull($options['streamsize'] ?? null, Cast::toInt(...));
        $this->multithreading = Cast::toValueOrNull($options['multithreading'] ?? null, Cast::toBool(...));
        $this->resumable = Cast::toValueOrNull($options['resumable'] ?? null, Cast::toBool(...));
        $this->resumableName = Cast::toValueOrNull($options['resumableName'] ?? null, Cast::toString(...));
        $this->resumableTimeout = Cast::toValueOrNull($options['resumableTimeout'] ?? null, Cast::toInt(...));
        $this->dateCache = Cast::toValueOrNull($options['dateCache'] ?? null, Cast::toInt(...));
        $this->noIndexErrors = Cast::toValueOrNull($options['noIndexErrors'] ?? null, Cast::toBool(...));
        $this->partitionMemory = Cast::toValueOrNull($options['partitionMemory'] ?? null, Cast::toInt(...));
        $this->table = Cast::toValueOrNull($options['table'] ?? null, Cast::toString(...));
        $this->dateFormat = Cast::toValueOrNull($options['dateFormat'] ?? null, Cast::toString(...));
        $this->timestampFormat = Cast::toValueOrNull($options['timestampFormat'] ?? null, Cast::toString(...));
        $this->terminatedBy = Cast::toValueOrNull($options['terminatedBy'] ?? null, Cast::toString(...));
        $this->enclosedBy = Cast::toValueOrNull($options['enclosedBy'] ?? null, Cast::toString(...));
        $this->optionallyEnclosedBy = Cast::toValueOrNull(
            $options['optionallyEnclosedBy'] ?? null,
            Cast::toString(...)
        );
        $this->characterset = Cast::toValueOrNull($options['characterset'] ?? null, Cast::toString(...));
        $this->degreeOfParallelism = Cast::toValueOrNull($options['degreeOfParallelism'] ?? null, Cast::toString(...));
        $this->trim = Cast::toValueOrNull($options['trim'] ?? null, Cast::toString(...));
        $this->csv = Cast::toValueOrNull($options['csv'] ?? null, Cast::toString(...));
        $this->nullif = Cast::toValueOrNull($options['nullif'] ?? null, Cast::toString(...));
        $this->fieldNames = Cast::toValueOrNull($options['fieldNames'] ?? null, Cast::toString(...));
        $this->dnfsEnable = Cast::toValueOrNull($options['dnfsEnable'] ?? null, Cast::toBool(...));
        $this->dnfsReadbuffers = Cast::toValueOrNull($options['dnfsReadbuffers'] ?? null, Cast::toInt(...));
        $this->sdfPrefix = Cast::toValueOrNull($options['sdfPrefix'] ?? null, Cast::toString(...));
        $this->displayHelp = Cast::toValueOrNull($options['displayHelp'] ?? null, Cast::toBool(...));
        $this->emptyLobsAreNull = Cast::toValueOrNull($options['emptyLobsAreNull'] ?? null, Cast::toBool(...));
        $this->defaults = Cast::toValueOrNull($options['defaults'] ?? null, Cast::toString(...));
        $this->directPathLockWait = Cast::toValueOrNull($options['directPathLockWait'] ?? null, Cast::toBool(...));
        $this->credential = Cast::toValueOrNull($options['credential'] ?? null, Cast::toString(...));
        $this->proxy = Cast::toValueOrNull($options['proxy'] ?? null, Cast::toString(...));

        return $this;
    }


    /** @inheritdoc */
    public function createShellArguments(array $shellArgs = []): array
    {
        $args = [
            'userid' => $this->logon->createShellArguments(),
            'control' => $this->control,
            'log' => $this->log,
            'bad' => $this->bad,
            'data' => $this->data,
            'discard' => $this->discard,
            'discardmax' => $this->discardmax,
            'skip' => $this->skip,
            'load' => $this->load,
            'errors' => $this->errors,
            'rows' => $this->rows,
            'bindsize' => $this->bindsize,
            'silent' => $this->silent,
            'direct' => $this->direct,
            'parfile' => $this->parfile,
            'parallel' => $this->parallel,
            'file' => $this->file,
            'skip_unusable_indexes' => $this->skipUnusableIndexes,
            'skip_index_maintenance' => $this->skipIndexMaintenance,
            'commit_discontinued' => $this->commitDiscontinued,
            'readsize' => $this->readsize,
            'external_table' => $this->externalTable,
            'columnarrayrows' => $this->columnarrayrows,
            'streamsize' => $this->streamsize,
            'multithreading' => $this->multithreading,
            'resumable' => $this->resumable,
            'resumable_name' => $this->resumableName,
            'resumable_timeout' => $this->resumableTimeout,
            'date_cache' => $this->dateCache,
            'no_index_errors' => $this->noIndexErrors,
            'partition_memory' => $this->partitionMemory,
            'table' => $this->table,
            'date_format' => $this->dateFormat,
            'timestamp_format' => $this->timestampFormat,
            'terminated_by' => $this->terminatedBy,
            'enclosed_by' => $this->enclosedBy,
            'optionally_enclosed_by' => $this->optionallyEnclosedBy,
            'characterset' => $this->characterset,
            'degree_of_parallelism' => $this->degreeOfParallelism,
            'trim' => $this->trim,
            'csv' => $this->csv,
            'nullif' => $this->nullif,
            'field_names' => $this->fieldNames,
            'dnfs_enable' => $this->dnfsEnable,
            'dnfs_readbuffers' => $this->dnfsReadbuffers,
            'sdf_prefix' => $this->sdfPrefix,
            'help' => $this->displayHelp,
            'empty_lobs_are_null' => $this->emptyLobsAreNull,
            'defaults' => $this->defaults,
            'direct_path_lock_wait' => $this->directPathLockWait,
            'credential' => $this->credential,
            'proxy' => $this->proxy,
        ];

        return parent::createShellArguments(array_map(
            fn(bool|int|string|null $value, string $key): string|null => match (true) {
                $value === null => null,
                $value === true => "{$key}=true",
                $value === false => "{$key}=false",
                default => "{$key}={$value}"
            },
            $args,
            array_keys($args)
        ));
    }
}
