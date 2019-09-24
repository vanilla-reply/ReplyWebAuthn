<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use Ramsey\Uuid\Uuid;
use Webauthn\PublicKeyCredentialSource;
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

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
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
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return $this->findAllByCustomerId($publicKeyCredentialUserEntity->getId());
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        if ($this->exists($publicKeyCredentialSource->getPublicKeyCredentialId())) {
            $this->update($publicKeyCredentialSource);
            return;
        }

        $this->insert($publicKeyCredentialSource);
    }

    /**
     * @param string $customerId
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByCustomerId(string $customerId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('customer_id = :id')
            ->setParameter('id', hex2bin($customerId));

        /** @var ResultStatement $result */
        $result = $query->execute();
        $values = $result->fetchAll(FetchMode::ASSOCIATIVE);

        return array_map(function(array $row) {
            return $this->hydrate($row);
        }, $values);
    }

    public function deleteById(string $credentialId): void
    {
        $this->connection->delete(self::TABLE_NAME, [
            'id' => $credentialId
        ]);
    }

    public function deleteByCustomerId(string $customerId): void
    {
        $this->connection->delete(self::TABLE_NAME, [
            'customer_id' => hex2bin($customerId)
        ]);
    }

    private function hydrate(array $values): NamedCredential
    {
        $source = new PublicKeyCredentialSource(
            $values['id'],
            $values['type'],
            json_decode($values['transports'], true),
            $values['attestation_type'],
            TrustPathLoader::loadTrustPath(json_decode($values['trust_path'], true)),
            Uuid::fromString($values['aaguid']),
            $values['public_key'],
            bin2hex($values['customer_id']),
            (int)$values['counter']
        );

        return new NamedCredential($source, $values['name']);
    }

    private function exists(string $credentialId): bool
    {
        return $this->findOneByCredentialId($credentialId) !== null;
    }

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
            'customer_id' => hex2bin($publicKeyCredentialSource->getUserHandle()),
            'counter' => $publicKeyCredentialSource->getCounter(),
            'created_at' => date($this->connection->getDatabasePlatform()->getDateTimeFormatString())
        ];
        if ($publicKeyCredentialSource instanceof NamedCredential) {
            $data['name'] = $publicKeyCredentialSource->getName();
        }
        $this->connection->insert(self::TABLE_NAME, $data);
    }

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
