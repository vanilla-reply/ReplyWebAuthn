<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Webauthn\PublicKeyCredentialSource as BaseSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TrustPath\TrustPathLoader;

class DoctrineCustomerCredentialRepository implements PublicKeyCredentialSourceRepository, CustomerCredentialRepository
{
    public const TABLE_NAME = 'customer_credential';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $publicKeyCredentialId
     * @return PublicKeyCredentialSource|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?BaseSource
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('id = :id')
            ->setParameter('id', $publicKeyCredentialId);

        /** @var ResultStatement $result */
        $result = $query->execute();
        $values = $result->fetch(FetchMode::ASSOCIATIVE);

        if (false === $values) {
            return null;
        }

        return $this->hydrate($values);
    }

    /**
     * @param PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('user_handle = :id')
            ->setParameter('id', $publicKeyCredentialUserEntity->getId());

        /** @var ResultStatement $result */
        $result = $query->execute();
        $values = $result->fetchAll(FetchMode::ASSOCIATIVE);

        return array_map(function (array $row) {
            return $this->hydrate($row);
        }, $values);
    }

    /**
     * @param BaseSource $publicKeyCredentialSource
     */
    public function saveCredentialSource(BaseSource $publicKeyCredentialSource): void
    {
        if (!$publicKeyCredentialSource instanceof PublicKeyCredentialSource) {
            throw new InvalidArgumentException(sprintf(
                'Argument has to be an instance of %s',
                PublicKeyCredentialSource::class
            ));
        }

        if ($this->exists($publicKeyCredentialSource->getPublicKeyCredentialId())) {
            $this->update($publicKeyCredentialSource);
            return;
        }

        $this->insert($publicKeyCredentialSource);
    }

    /**
     * @param string $credentialId
     */
    public function deleteById(string $credentialId): void
    {
        $this->connection->delete(self::TABLE_NAME, [
            'id' => $credentialId
        ]);
    }

    /**
     * @param PublicKeyCredentialUserEntity $userEntity
     */
    public function deleteAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        $this->connection->delete(self::TABLE_NAME, [
            'user_handle' => $userEntity->getId()
        ]);
    }

    /**
     * @param array $values
     * @return PublicKeyCredentialSource
     */
    private function hydrate(array $values): PublicKeyCredentialSource
    {
        $entity = new PublicKeyCredentialSource(
            $values['id'],
            $values['type'],
            json_decode($values['transports'], true),
            $values['attestation_type'],
            TrustPathLoader::loadTrustPath(json_decode($values['trust_path'], true)),
            Uuid::fromString($values['aaguid']),
            $values['public_key'],
            $values['user_handle'],
            (int)$values['counter']
        );
        $entity->setName($values['name']);
        $entity->setCreatedAt(new DateTimeImmutable($values['created_at']));
        if ($values['updated_at'] !== null) {
            $entity->setUpdatedAt(new DateTimeImmutable($values['updated_at']));
        }

        return $entity;
    }

    /**
     * @param string $credentialId
     * @return bool
     */
    private function exists(string $credentialId): bool
    {
        return $this->findOneByCredentialId($credentialId) !== null;
    }

    /**
     * @param PublicKeyCredentialSource $publicKeyCredentialSource
     */
    private function insert(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $data = [
            'id' => $publicKeyCredentialSource->getPublicKeyCredentialId(),
            'type' => $publicKeyCredentialSource->getType(),
            'transports' => json_encode($publicKeyCredentialSource->getTransports()),
            'attestation_type' => $publicKeyCredentialSource->getAttestationType(),
            'trust_path' => json_encode($publicKeyCredentialSource->getTrustPath()),
            'aaguid' => $publicKeyCredentialSource->getAaguid()->toString(),
            'public_key' => $publicKeyCredentialSource->getCredentialPublicKey(),
            'user_handle' => $publicKeyCredentialSource->getUserHandle(),
            'counter' => $publicKeyCredentialSource->getCounter(),
            'created_at' => date($this->connection->getDatabasePlatform()->getDateTimeFormatString()),
            'name' => $publicKeyCredentialSource->getName()
        ];
        $this->connection->insert(self::TABLE_NAME, $data);
    }

    /**
     * @param PublicKeyCredentialSource $publicKeyCredentialSource
     */
    private function update(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $data = [
            'counter' => $publicKeyCredentialSource->getCounter(),
            'updated_at' => date($this->connection->getDatabasePlatform()->getDateTimeFormatString())
        ];

        $this->connection->update(
            self::TABLE_NAME,
            $data,
            ['id' => $publicKeyCredentialSource->getPublicKeyCredentialId()]
        );
    }
}
