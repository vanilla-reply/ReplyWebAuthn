<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge\Oauth;

use DateInterval;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reply\WebAuthn\Bridge\UserIdResolver;
use Reply\WebAuthn\Bridge\UserVerificationService;
use Reply\WebAuthn\Exception\AuthFailedException;
use Shopware\Core\Framework\Api\OAuth\User\User;

class WebAuthnGrant extends AbstractGrant
{
    /**
     * @var UserVerificationService
     */
    private $userVerificationService;

    /** @var UserIdResolver */
    private $userIdResolver;

    /**
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param UserVerificationService $userVerificationService
     */
    public function __construct(
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        UserVerificationService $userVerificationService,
        UserIdResolver $userIdResolver
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->setRefreshTokenTTL(new \DateInterval('P1M'));
        $this->userVerificationService = $userVerificationService;
        $this->userIdResolver = $userIdResolver;
    }

    public function getIdentifier(): string
    {
        return 'webauthn';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        $this->setClientRepository(new ClientRepository());

        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        // Issue and persist new tokens
        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $finalizedScopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        // Send events to emitter
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request));
        $this->getEmitter()->emit(new RequestEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request));

        // Inject tokens into response
        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return UserEntityInterface
     */
    protected function validateUser(ServerRequestInterface $request): UserEntityInterface
    {
        $username = (string)$this->getRequestParameter('username', $request);

        $userId = $this->userIdResolver->getUserIdByName($username);

        try {
            $userId = $this->userVerificationService->verify($request, $userId);
        } catch (AuthFailedException $e) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidCredentials();
        }

        return new User(bin2hex($userId));
    }
}
