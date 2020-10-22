<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use Webauthn\PublicKeyCredentialCreationOptions;

class PublicKeyCredentialCreationOptionsRepository
{
    public const TABLE_NAME = 'webauthn_creation_options';

    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(PublicKeyCredentialCreationOptions $creationOptions): void
    {
        $this->connection->executeQuery(
            'REPLACE INTO `' . static::TABLE_NAME .'`
                    (`user_handle`,`payload`,`created_at`)
                    VALUES
                    (:user_handle, :payload, :created_at);',
            [
                'user_handle' => $creationOptions->getUser()->getId(),
                'payload' => json_encode($creationOptions),
                'created_at' => date($this->connection->getDatabasePlatform()->getDateTimeFormatString())
            ]
        );
    }

    public function fetch(string $userHandle): ?PublicKeyCredentialCreationOptions
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

        return PublicKeyCredentialCreationOptions::createFromString($values['payload']);
    }
}
