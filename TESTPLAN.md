# Test Plan

Walk through this list before tagging a release or merging a change that touches `composer.json`, the analyzers, or the test suite. Run every command from the repository root.

## Setup

- [ ] `composer install` finishes without errors and without asking to change the lock file.
- [ ] `composer validate` reports the manifest is valid.
- [ ] `working/` exists after the first run and is not shown in `git status`.

## Automated checks

- [ ] `composer phpcs` reports no errors and no warnings for `src/`, `bundle/`, and `tests/`.
- [ ] `composer phpstan` reports `No errors` at level 10 for `src/`, `bundle/`, and `tests/`.
- [ ] `composer test` passes with no warnings, deprecations, or notices in the output.
- [ ] `composer check` runs all three in order and exits 0.

## Functional coverage

Confirm each area has passing tests under `tests/` that cover the listed behaviors.

- [ ] `LogonOptions`: registers `USER`, `PASS`, `CONN`, `SYS`, and `ED` as value options and `NOLOG` as a flag; reads every option from input; keeps constructor values for options not given; a missing `NOLOG` flag turns `noLog` off; builds `user/pass@conn`, adds `@` only when the identifier lacks one, adds `AS ` and `EDITION=` only when missing; returns `null` under `NOLOG`; returns `/` with nothing set.
- [ ] `OracleOptions`: registers the logon options; registers string map entries as required-value options and required arguments and array entries with the given mode; delegates parsing to the logon; prepends the command and drops nulls from the argument list; uses an empty string when there is no command.
- [ ] `SqlPlusOptions`: defaults to `sqlplus`; honors a custom executable; registers `C`, `M`, `R` as value options, `F`, `L`, `NOLOGINTIME`, `S` as flags, `script` as a required argument and `parameters` as an optional array; reads every option and joins script and parameters; leaves unset options at their defaults; orders the argument list as switches, logon, script; uses `/NOLOG` under `NOLOG`; keeps a script that already starts with `@`; appends extra arguments.
- [ ] `SqlLoaderOptions`: defaults to `sqlldr`; honors a custom executable; registers all 52 parameters plus the logon options; every one of the 51 value parameters round-trips from the command line to its `name=value` form with Oracle's snake_case spelling, booleans as `true`/`false`, integers typed; `--userid` is ignored in favor of the logon; `userid` comes first and unset parameters are dropped; appends extra arguments.
- [ ] `OracleCommand`: is a `Gadget\Console\Command\ShellCommand` and a logger proxy; registers the options on itself; returns exactly one command line built from the parsed input.
- [ ] `SqlPlusCommand` through `CommandTester` against the echoing fixture: named `oracle:sqlplus` with its description; passes `-S`, the logon string, and `@script params` as separate arguments; passes `/NOLOG` when asked; a non-zero exit throws `ProcessException` with `Invalid exit code: N`; with `throwOnError` false the command returns `SUCCESS`.
- [ ] `SqlLoaderCommand` through `CommandTester` against the echoing fixture: named `oracle:sqlldr` with its description; passes `userid=` first then each `name=value`; a non-zero exit throws; with `throwOnError` false the command returns `SUCCESS`.
- [ ] `OracleDriver`: extends DBAL's `AbstractOracleDriver`; its platform is an `OraclePlatform` whose date format string is `d-M-y H.i.s.u A`; `persistent` together with `exclusive` throws DBAL's `InvalidConfiguration`.
- [ ] `GadgetOracleBundle`: extends `Bundle` and exposes `GadgetOracleExtension` under the alias `gadget_oracle`.
- [ ] `GadgetOracleExtension`: registers both commands, the three options classes, and the driver as autowired, autoconfigured services; does not register the abstract `OracleCommand` and `OracleOptions`; binds `LogonOptions` to `%env(default::ORACLE_USER)%`, `ORACLE_PASS`, and `ORACLE_CONN`; a compiled container with a `ProcessShellEnv` builds both commands with null credentials when the variables are unset and with the variables' values when they are set.

## Consuming the package

These need Oracle Instant Client and a database to talk to, so they are manual.

- [ ] In a scratch project, `composer require gsu-cetloe/gadget-oracle-php` installs `gsu-cetloe/gadget-console-php`, `gsu-cetloe/gadget-core-php`, `gsu-cetloe/gadget-process-php`, `gsu-cetloe/symfony-bundle`, `doctrine/dbal` 4, and `symfony/console` 6.4.
- [ ] In a scratch Symfony app with `GadgetProcessBundle` and `GadgetOracleBundle` enabled and `ORACLE_USER`, `ORACLE_PASS`, and `ORACLE_CONN` set, `bin/console oracle:sqlplus` lists the logon and SQL*Plus options under `--help`, and running it with a script that does `SELECT 1 FROM dual;` prints the result and exits 0.
- [ ] In that app, `bin/console oracle:sqlplus report.sql 2026 FALL` reaches the script with `&1` and `&2` set to `2026` and `FALL`. This is the check for the single-argument script hand-off described under Known gaps.
- [ ] In that app, `bin/console oracle:sqlplus --M="CSV ON" ...` produces CSV output, or does not; record which, since the switch is passed with the quotes included.
- [ ] In that app, `bin/console oracle:sqlldr --control=... --data=...` loads the rows and the log file lands where `--log` says.
- [ ] A script that fails, or a load with more than `--errors` bad rows, makes the command exit non-zero and the exception message names the tool's exit code.
- [ ] With `ext-oci8` loaded, a DBAL connection using `driverClass => OracleDriver::class` runs `SELECT SYSDATE FROM dual` and a date bound through the platform's date format is accepted by the server.

## Known gaps

- The suite does not run SQL*Plus or SQL*Loader. The command tests prove the argument list, not that Oracle's tools accept it. In particular the script name and its parameters are passed as one argument (`@report.sql 2026 FALL`), `-M` is passed as `-M "CSV ON"` with literal quotes, and `-R` as `-R 3`. That is how the original package built them. Whether SQL*Plus parses them that way is one of the manual checks above and has not been confirmed.
- `OracleDriver::connect()` is only tested up to the persistent-and-exclusive check. The three `oci_*connect` branches and `ConnectionFailed` need `ext-oci8`, which the development machine does not have.
- `--userid` on `oracle:sqlldr` is accepted and ignored. The tests pin that so a change shows up.
- A `noLog: true` passed to the `LogonOptions` constructor is lost when the command parses input without `--NOLOG`. The tests pin the current behavior.
- The suite checks that the services are autoconfigured but does not boot FrameworkBundle to prove the commands are tagged `console.command`. The manual steps above cover that.
- Tests run on whatever PHP is installed locally. Composer resolves dependencies as if PHP were 8.1.0, but the behavior checked here is that of the local interpreter.
