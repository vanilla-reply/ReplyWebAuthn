<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use Webauthn\PublicKeyCredentialRequestOptions;

class PublicKeyCredentialRequestOptionsRepository
{
    public const TABLE_NAME = 'webauthn_request_options';

    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(string $userHandle, PublicKeyCredentialRequestOptions $requestOptions): void
    {
        $this->connection->executeQuery(
            'REPLACE INTO `' . static::TABLE_NAME . '`
                    (`user_handle`,`payload`,`created_at`)
                    VALUES
                    (:user_handle, :payload, :created_at);',
            [
                'user_handle' => $userHandle,
                'payload' => json_encode($requestOptions),
                'created_at' => date($this->connection->getDatabasePlatform()->getDateTimeFormatString())
            ]
        );
    }

    public function fetch(string $userHandle): ?PublicKeyCredentialRequestOptions
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(static::TABLE_NAME)
            ->where('user_handle = :user_handle')
            ->setParameter('user_handle', $userHandle);

        /** @var ResultStatement $result */
        $result = $query->execute();
        $values = $result->fetch(FetchMode::ASSOCIATIVE);

        if (false === $values) {
            return null;
        }

        return PublicKeyCredentialRequestOptions::createFromString($values['payload']);
    }
}
