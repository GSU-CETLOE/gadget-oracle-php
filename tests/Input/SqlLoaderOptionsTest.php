<?php

declare(strict_types=1);

namespace Gadget\Oracle\Tests\Input;

use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\SqlLoaderOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

final class SqlLoaderOptionsTest extends TestCase
{
    /**
     * A command with the SQL*Loader options registered on it.
     *
     * @param SqlLoaderOptions $options
     * @return Command
     */
    private static function configuredCommand(SqlLoaderOptions $options): Command
    {
        $command = new Command('test');
        $options->configureCommand($command);
        return $command;
    }


    /**
     * Parses command-line values through a command configured with the options.
     *
     * @param SqlLoaderOptions $options
     * @param array<string, string|bool> $values
     * @return SqlLoaderOptions
     */
    private static function parse(SqlLoaderOptions $options, array $values): SqlLoaderOptions
    {
        return $options->parseInput(new ArrayInput($values, self::configuredCommand($options)->getDefinition()));
    }


    public function testDefaultsToTheSqlLoaderExecutableOnThePath(): void
    {
        self::assertSame('sqlldr', (new SqlLoaderOptions(new LogonOptions()))->command);
    }


    public function testUsesTheConfiguredExecutable(): void
    {
        $options = new SqlLoaderOptions(new LogonOptions(), '/opt/oracle/bin/sqlldr');

        self::assertSame('/opt/oracle/bin/sqlldr', $options->createShellArguments()[0]);
    }


    /**
     * Every SQL*Loader option: the command-line name, a value to give it, and
     * the argument SQL*Loader should receive.
     *
     * @return \Generator<string, array{string, string, string}>
     */
    public static function providesOptions(): \Generator
    {
        yield 'control' => ['control', 'load.ctl', 'control=load.ctl'];
        yield 'log' => ['log', 'load.log', 'log=load.log'];
        yield 'bad' => ['bad', 'load.bad', 'bad=load.bad'];
        yield 'data' => ['data', 'load.dat', 'data=load.dat'];
        yield 'discard' => ['discard', 'load.dsc', 'discard=load.dsc'];
        yield 'discardmax' => ['discardmax', '5', 'discardmax=5'];
        yield 'skip' => ['skip', '10', 'skip=10'];
        yield 'load' => ['load', '1000', 'load=1000'];
        yield 'errors' => ['errors', '50', 'errors=50'];
        yield 'rows' => ['rows', '5000', 'rows=5000'];
        yield 'bindsize' => ['bindsize', '256000', 'bindsize=256000'];
        yield 'silent' => ['silent', 'header,feedback', 'silent=header,feedback'];
        yield 'direct' => ['direct', 'true', 'direct=true'];
        yield 'parfile' => ['parfile', 'load.par', 'parfile=load.par'];
        yield 'parallel' => ['parallel', 'false', 'parallel=false'];
        yield 'file' => ['file', 'users01.dbf', 'file=users01.dbf'];
        yield 'skipUnusableIndexes' => ['skipUnusableIndexes', 'yes', 'skip_unusable_indexes=true'];
        yield 'skipIndexMaintenance' => ['skipIndexMaintenance', 'no', 'skip_index_maintenance=false'];
        yield 'commitDiscontinued' => ['commitDiscontinued', 'true', 'commit_discontinued=true'];
        yield 'readsize' => ['readsize', '1048576', 'readsize=1048576'];
        yield 'externalTable' => ['externalTable', 'EXECUTE', 'external_table=EXECUTE'];
        yield 'columnarrayrows' => ['columnarrayrows', '5000', 'columnarrayrows=5000'];
        yield 'streamsize' => ['streamsize', '256000', 'streamsize=256000'];
        yield 'multithreading' => ['multithreading', 'true', 'multithreading=true'];
        yield 'resumable' => ['resumable', 'true', 'resumable=true'];
        yield 'resumableName' => ['resumableName', 'nightly', 'resumable_name=nightly'];
        yield 'resumableTimeout' => ['resumableTimeout', '7200', 'resumable_timeout=7200'];
        yield 'dateCache' => ['dateCache', '1000', 'date_cache=1000'];
        yield 'noIndexErrors' => ['noIndexErrors', 'true', 'no_index_errors=true'];
        yield 'partitionMemory' => ['partitionMemory', '0', 'partition_memory=0'];
        yield 'table' => ['table', 'STUDENTS', 'table=STUDENTS'];
        yield 'dateFormat' => ['dateFormat', 'YYYY-MM-DD', 'date_format=YYYY-MM-DD'];
        yield 'timestampFormat' => [
            'timestampFormat',
            'YYYY-MM-DD HH24:MI:SS',
            'timestamp_format=YYYY-MM-DD HH24:MI:SS'
        ];
        yield 'terminatedBy' => ['terminatedBy', ',', 'terminated_by=,'];
        yield 'enclosedBy' => ['enclosedBy', '"', 'enclosed_by="'];
        yield 'optionallyEnclosedBy' => ['optionallyEnclosedBy', "'", "optionally_enclosed_by='"];
        yield 'characterset' => ['characterset', 'AL32UTF8', 'characterset=AL32UTF8'];
        yield 'degreeOfParallelism' => ['degreeOfParallelism', 'AUTO', 'degree_of_parallelism=AUTO'];
        yield 'trim' => ['trim', 'LRTRIM', 'trim=LRTRIM'];
        yield 'csv' => ['csv', 'WITH_EMBEDDED', 'csv=WITH_EMBEDDED'];
        yield 'nullif' => ['nullif', 'BLANKS', 'nullif=BLANKS'];
        yield 'fieldNames' => ['fieldNames', 'FIRST', 'field_names=FIRST'];
        yield 'dnfsEnable' => ['dnfsEnable', 'true', 'dnfs_enable=true'];
        yield 'dnfsReadbuffers' => ['dnfsReadbuffers', '4', 'dnfs_readbuffers=4'];
        yield 'sdfPrefix' => ['sdfPrefix', '/data/lobs/', 'sdf_prefix=/data/lobs/'];
        yield 'displayHelp' => ['displayHelp', 'true', 'help=true'];
        yield 'emptyLobsAreNull' => ['emptyLobsAreNull', 'true', 'empty_lobs_are_null=true'];
        yield 'defaults' => ['defaults', 'EVALUATE_ONCE', 'defaults=EVALUATE_ONCE'];
        yield 'directPathLockWait' => ['directPathLockWait', 'true', 'direct_path_lock_wait=true'];
        yield 'credential' => ['credential', 'OBJ_STORE_CRED', 'credential=OBJ_STORE_CRED'];
        yield 'proxy' => ['proxy', 'proxy.example.edu:3128', 'proxy=proxy.example.edu:3128'];
    }


    #[DataProvider('providesOptions')]
    public function testParsesEachOptionAndPassesItToSqlLoader(string $name, string $value, string $expected): void
    {
        $options = self::parse(new SqlLoaderOptions(new LogonOptions()), ['--NOLOG' => true, '--' . $name => $value]);

        self::assertSame(['sqlldr', $expected], array_values($options->createShellArguments()));
    }


    public function testConfigureCommandRegistersEveryOptionAsTakingAValue(): void
    {
        $definition = self::configuredCommand(new SqlLoaderOptions(new LogonOptions()))->getDefinition();

        $expected = ['USER', 'PASS', 'CONN', 'SYS', 'ED', 'NOLOG', 'userid'];
        foreach (self::providesOptions() as [$name]) {
            $expected[] = $name;
        }
        self::assertEqualsCanonicalizing($expected, array_keys($definition->getOptions()));

        foreach ($definition->getOptions() as $option) {
            if ($option->getName() !== 'NOLOG') {
                self::assertTrue($option->isValueRequired(), "{$option->getName()} should take a value");
            }
        }
    }


    public function testParseInputTypesTheValues(): void
    {
        $options = self::parse(new SqlLoaderOptions(new LogonOptions()), [
            '--control' => 'load.ctl',
            '--skip' => '10',
            '--direct' => 'true',
        ]);

        self::assertSame('load.ctl', $options->control);
        self::assertSame(10, $options->skip);
        self::assertTrue($options->direct);
        self::assertNull($options->log);
        self::assertNull($options->parallel);
    }


    public function testParseInputReturnsTheOptions(): void
    {
        $options = new SqlLoaderOptions(new LogonOptions());
        $input = new ArrayInput([], self::configuredCommand($options)->getDefinition());

        self::assertSame($options, $options->parseInput($input));
    }


    /**
     * The userid option is registered so it shows in help, but the value SQL*Loader
     * receives always comes from the logon options.
     */
    public function testUseridComesFromTheLogonOptions(): void
    {
        $options = self::parse(new SqlLoaderOptions(new LogonOptions()), [
            '--userid' => 'ignored/ignored',
            '--USER' => 'scott',
            '--PASS' => 'tiger',
        ]);

        self::assertSame(['sqlldr', 'userid=scott/tiger'], array_values($options->createShellArguments()));
    }


    public function testCreateShellArgumentsPutsUseridFirstAndDropsUnsetOptions(): void
    {
        $options = new SqlLoaderOptions(
            new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: 'orcl'),
            control: 'load.ctl',
            skip: 10,
            direct: true,
            parallel: false
        );

        self::assertSame(
            ['sqlldr', 'userid=scott/tiger@orcl', 'control=load.ctl', 'skip=10', 'direct=true', 'parallel=false'],
            array_values($options->createShellArguments())
        );
    }


    public function testCreateShellArgumentsWithNothingSet(): void
    {
        $options = new SqlLoaderOptions(new LogonOptions());

        self::assertSame(['sqlldr', 'userid=/'], array_values($options->createShellArguments()));
    }


    public function testCreateShellArgumentsAppendsExtraArguments(): void
    {
        $options = new SqlLoaderOptions(new LogonOptions(noLog: true));

        self::assertSame(['sqlldr', 'extra=1'], array_values($options->createShellArguments(['extra=1', null])));
    }
}
