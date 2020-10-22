<?php

namespace Reply\WebAuthn\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;

class UserIdResolver
{
    public const TABLE_NAME = 'user';

    /** @var Connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getUserIdByName(string $username): ?string
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(static::TABLE_NAME)
            ->where('username = :username')
            ->setParameter('username', $username);

        /** @var ResultStatement $result */
        $result = $query->execute();
        $value  = $result->fetch(FetchMode::ASSOCIATIVE);

        if (false === $value) {
            return null;
        }

        return $value['id'];
    }
}
