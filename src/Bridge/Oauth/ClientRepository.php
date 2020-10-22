<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge\Oauth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Shopware\Core\Framework\Api\OAuth\Client\ApiClient;

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true): ?ClientEntityInterface
    {
        if ($grantType === 'webauthn' && $clientIdentifier === 'administration') {
            return new ApiClient('administration', true);
        }

        return null;
    }
}
