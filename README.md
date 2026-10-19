# gadget-oracle-php

Oracle Database pieces for a Symfony app. It gives you:

- Two [Symfony Console](https://symfony.com/doc/6.4/components/console.html) commands, `oracle:sqlplus` and `oracle:sqlldr`, that run Oracle's own SQL*Plus and SQL*Loader executables in a child process. The command-line options map onto the switches those tools take, and the credentials can come from environment variables.
- A [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html) driver for the OCI8 extension that formats dates the way the Oracle session expects.
- A Symfony bundle that wires all of it up.

CETLOE at Georgia State University maintains this package. It started as `mjfk23/gadget-oracle`, written by Melody Kimball.

## Requirements

- PHP 8.1
- Symfony Console 6.4 and Doctrine DBAL 4.2
- Oracle Instant Client with the `sqlplus` and `sqlldr` tools, if you use the commands. They are not bundled; the commands look for them on the `PATH` unless you give a full path.
- The `oci8` PHP extension, if you use the Doctrine driver. Composer suggests it rather than requiring it, so the commands work on a machine without it.

The package pulls in `gsu-cetloe/gadget-console-php`, `gsu-cetloe/gadget-core-php`, `gsu-cetloe/gadget-process-php`, and `gsu-cetloe/symfony-bundle`.

## Setup

Install [Composer](https://getcomposer.org/) if you don't have it, then add the package to your project:

```sh
composer require gsu-cetloe/gadget-oracle-php
```

To work on the library itself, clone the repository and install the development dependencies:

```sh
git clone https://github.com/GSU-CETLOE/gadget-oracle-php.git
cd gadget-oracle-php
composer install
```

Your machine does not have to run PHP 8.1. Composer is told to resolve dependencies as if it were PHP 8.1.0 no matter what version runs it, so the lock file always reflects what the library targets. Before a release, run the suite on an actual 8.1 build.

## Development commands

| Command | What it does |
| --- | --- |
| `composer test` | Runs the PHPUnit suite in `tests/`. |
| `composer phpstan` | Runs PHPStan at level 10 over `src/`, `bundle/`, and `tests/`. |
| `composer phpcs` | Checks `src/`, `bundle/`, and `tests/` against PSR-12. |
| `composer check` | Runs all three in order. Run this before you commit. |

Anything generated, such as the PHPStan and PHPUnit caches, lands in `working/`. Git ignores that directory.

## What's in the box

Everything lives under the `Gadget\Oracle` namespace.

| Class | Purpose |
| --- | --- |
| `Command\SqlPlusCommand` | The `oracle:sqlplus` command. Runs a SQL*Plus script. |
| `Command\SqlLoaderCommand` | The `oracle:sqlldr` command. Runs a SQL*Loader load. |
| `Command\OracleCommand` | Abstract. The shared base: configures the command from an options object and turns the parsed input into one command line. |
| `Input\LogonOptions` | Username, password, connect identifier, `AS SYSDBA`-style privilege, and session edition. Builds the `user/pass@conn` string both tools take. |
| `Input\SqlPlusOptions` | The SQL*Plus switches plus the script and its parameters. |
| `Input\SqlLoaderOptions` | The SQL*Loader parameters, all of them. |
| `Input\OracleOptions` | Abstract. Registers an options map and an arguments map on a command and reads them back. |
| `Doctrine\OracleDriver` | A DBAL OCI8 driver with an Oracle-style date format string. |
| `GadgetOracleBundle` | Registers the above with a Symfony container. |

## Logging on

Both commands take the same six logon options:

| Option | Meaning |
| --- | --- |
| `--USER` | Database account username |
| `--PASS` | Database account password |
| `--CONN` | Connect identifier, either a Net Service Name or an Easy Connect string |
| `--SYS` | Administrative privilege, `SYSDBA` or `SYSOPER`. The `AS ` prefix is optional. |
| `--ED` | Session edition. The `EDITION=` prefix is optional. |
| `--NOLOG` | Don't log on at all. SQL*Plus gets `/NOLOG`; SQL*Loader gets no `userid` parameter. |

They are joined into the string Oracle expects, so `--USER=scott --PASS=tiger --CONN=orcl --SYS=SYSDBA` becomes `scott/tiger@orcl AS SYSDBA`. With no options at all the string is `/`, which asks Oracle for operating-system authentication.

When the bundle is enabled, `LogonOptions` is built from the `ORACLE_USER`, `ORACLE_PASS`, and `ORACLE_CONN` environment variables. Anything you pass on the command line replaces the value from the environment for that run. Keep in mind that the password ends up in the child process's argument list, where other users on the box can read it with `ps`. That is how SQL*Plus and SQL*Loader have always worked; it is not something this package can fix.

## oracle:sqlplus

```
oracle:sqlplus [options] <script> [<parameters>...]
```

`script` is the SQL*Plus script to run, as a file name or a URL. The leading `@` is added if you leave it off. Any `parameters` after it are handed to the script as substitution variables.

| Option | SQL*Plus switch | Meaning |
| --- | --- | --- |
| `--C=x.y[.z]` | `-C` | Compatibility version for affected commands |
| `--F` | `-F` | Fast mode: changes several default settings for better throughput |
| `--L` | `-L` | Try to log on once instead of reprompting on a failure |
| `--M=...` | `-M` | Automatic HTML or CSV markup of output |
| `--NOLOGINTIME` | `-NOLOGINTIME` | Don't print the last successful login time |
| `--R=1\|2\|3` | `-R` | Restricted mode; 3 disables every command that touches the file system |
| `--S` | `-S` | Silent mode: no banner, prompts, or command echo |

So this invocation:

```sh
bin/console oracle:sqlplus report.sql 2026 FALL --S --USER=scott --PASS=tiger --CONN=orcl
```

starts SQL*Plus with these arguments:

```
sqlplus -S scott/tiger@orcl "@report.sql 2026 FALL"
```

The quotes are there to show that the script name and its parameters travel as one argument. The command does not go through a shell. Every argument is passed to the executable exactly as built, which also means `--M="CSV ON"` arrives as the single argument `-M "CSV ON"`, quotes included, and `--R=3` as `-R 3`. We inherited that from the original package and have not changed it; see the Known gaps section of [TESTPLAN.md](TESTPLAN.md).

## oracle:sqlldr

```
oracle:sqlldr [options]
```

Every SQL*Loader parameter is an option, spelled in camelCase, and is passed through as `name=value` with Oracle's own spelling. A few examples:

| Option | SQL*Loader parameter |
| --- | --- |
| `--control=load.ctl` | `control=load.ctl` |
| `--data=students.csv` | `data=students.csv` |
| `--skip=1` | `skip=1` |
| `--direct=true` | `direct=true` |
| `--skipUnusableIndexes=true` | `skip_unusable_indexes=true` |
| `--externalTable=EXECUTE` | `external_table=EXECUTE` |
| `--displayHelp=true` | `help=true` |

Run `bin/console oracle:sqlldr --help` for the full list of 52. Boolean parameters are true when the value starts with `1`, `O`, `T`, `X`, or `Y` in either case, so `true`, `yes`, `on`, and `1` all work; anything else, including `false` and `no`, is false. Numeric parameters are cast to integers.

The logon goes first, as `userid=scott/tiger@orcl`. There is also a `--userid` option in the help output, but the value you give it is ignored; use `--USER`, `--PASS`, and `--CONN`.

```sh
bin/console oracle:sqlldr --control=students.ctl --data=students.csv --skip=1 --direct=true
```

with the three `ORACLE_*` environment variables set runs:

```
sqlldr userid=scott/tiger@orcl control=students.ctl data=students.csv skip=1 direct=true
```

## Exit codes and output

The commands come from `gsu-cetloe/gadget-console-php`, so they behave like any `ShellCommand` there. Everything the Oracle tool writes is echoed to the console as it arrives and logged, standard output at `info` and standard error at `error`. A non-zero exit from the tool throws `Gadget\Process\ProcessException` with the message `Invalid exit code: N`. Construct the command with `throwOnError: false` if you would rather swallow it; the command then returns `SUCCESS` regardless.

## Building the commands yourself

Outside a Symfony app, build the options and hand them to the command with a `ProcessShellEnv` for the working directory and environment. The second constructor argument of each options class is the path to the executable.

```php
use Gadget\Oracle\Command\SqlPlusCommand;
use Gadget\Oracle\Input\LogonOptions;
use Gadget\Oracle\Input\SqlPlusOptions;
use Gadget\Process\ProcessShellEnv;

$logon = new LogonOptions(username: 'scott', password: 'tiger', connectIdentifier: 'orcl');
$options = new SqlPlusOptions($logon, '/opt/oracle/instantclient/sqlplus', S: true);

$command = new SqlPlusCommand($options, new ProcessShellEnv('/srv/app'));
```

Values set in the constructor are the defaults; what the user types on the command line replaces them. The one exception is `--NOLOG`: Symfony reports a missing flag as false, so a `noLog: true` in the constructor is switched off whenever the flag is not on the command line.

## Doctrine driver

`OracleDriver` is DBAL's own OCI8 driver with one change: the platform's date format string is `d-M-y H.i.s.u A` instead of DBAL's default `Y-m-d 00:00:00`. Point a connection at it with `driverClass`:

```php
use Doctrine\DBAL\DriverManager;
use Gadget\Oracle\Doctrine\OracleDriver;

$connection = DriverManager::getConnection([
    'driverClass' => OracleDriver::class,
    'user' => 'scott',
    'password' => 'tiger',
    'host' => 'db.example.edu',
    'port' => 1521,
    'servicename' => 'orcl',
]);
```

The `persistent` parameter and the `exclusive` driver option work as they do in DBAL's driver, and asking for both at once throws DBAL's `InvalidConfiguration`. The driver needs the `oci8` extension at connect time.

## Symfony bundle

Add the bundle to `config/bundles.php`, together with the process bundle that provides the `ProcessShellEnv` the commands need:

```php
return [
    // ...
    Gadget\Process\GadgetProcessBundle::class => ['all' => true],
    Gadget\Oracle\GadgetOracleBundle::class => ['all' => true],
];
```

The bundle registers both commands, the three options classes, and `OracleDriver` as autowired, autoconfigured services, and binds `LogonOptions` to the `ORACLE_USER`, `ORACLE_PASS`, and `ORACLE_CONN` environment variables. A variable that is not set leaves its field null. The abstract `OracleCommand` and `OracleOptions` are not registered. If you also enable `Gadget\Console\GadgetConsoleBundle`, every run of the commands gets a `Started:` and `Terminated:` line in the log.

## Testing

`composer test` runs the suite. The command tests run a real child process; instead of SQL*Plus or SQL*Loader they point the command at a small PHP script in `tests/Fixtures/` that prints each argument it received, so the tests check the exact argument list the real tool would get. The bundle tests compile a real `ContainerBuilder` and resolve the `ORACLE_*` variables. The driver tests stop short of opening a connection, because that needs the `oci8` extension and a database. [TESTPLAN.md](TESTPLAN.md) has the manual walkthrough.

## License

MIT. See [LICENSE](LICENSE).
