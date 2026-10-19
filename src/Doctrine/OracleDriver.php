<?php

declare(strict_types=1);

namespace Gadget\Oracle\Doctrine;

use Doctrine\DBAL\Driver\AbstractOracleDriver;
use Doctrine\DBAL\Driver\OCI8\Connection;
use Doctrine\DBAL\Driver\OCI8\Exception\ConnectionFailed;
use Doctrine\DBAL\Driver\OCI8\Exception\InvalidConfiguration;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\ServerVersionProvider;

use function oci_connect;
use function oci_new_connect;
use function oci_pconnect;

use const OCI_NO_AUTO_COMMIT;

final class OracleDriver extends AbstractOracleDriver
{
    public function getDatabasePlatform(ServerVersionProvider $versionProvider): OraclePlatform
    {
        return new class extends OraclePlatform {
            public function getDateFormatString(): string
            {
                return 'd-M-y H.i.s.u A'; //'Y-m-d 00:00:00';
            }
        };
    }


    /** @inheritDoc} */
    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): Connection {
        $args = [
            $params['user'] ?? '',
            $params['password'] ?? '',
            $this->getEasyConnectString($params),
            $params['charset'] ?? '',
            $params['sessionMode'] ?? OCI_NO_AUTO_COMMIT
        ];

        $persistent = ($params['persistent'] ?? null) === true;
        $exclusive = ($params['driverOptions']['exclusive'] ?? null) === true;
        if ($persistent && $exclusive) {
            throw InvalidConfiguration::forPersistentAndExclusive();
        }

        $connection = match (true) {
            $persistent => @oci_pconnect(...$args),
            $exclusive => @oci_new_connect(...$args),
            default => @oci_connect(...$args)
        };

        if ($connection === false) {
            throw ConnectionFailed::new();
        }

        return new Connection($connection);
    }
}
