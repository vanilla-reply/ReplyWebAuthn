<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;

interface CustomerCredentialRepository extends PublicKeyCredentialSourceRepository
{
    /**
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByCustomerId(string $customerId): array;

    /**
     * @param CustomerEntity $customerEntity
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByCustomer(CustomerEntity $customerEntity): array;

    /**
     * @param string $credentialId
     */
    public function deleteById(string $credentialId): void;

    /**
     * @param string $customerId
     */
    public function deleteByCustomerId(string $customerId): void;
}
