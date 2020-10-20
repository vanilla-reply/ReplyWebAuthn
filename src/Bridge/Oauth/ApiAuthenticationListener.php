<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge\Oauth;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Reply\WebAuthn\Bridge\UserVerificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiAuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var RefreshTokenRepositoryInterface
     */
    private $refreshTokenRepository;

    /**
     * @var UserVerificationService
     */
    private $userVerificationService;

    public function __construct(AuthorizationServer $authorizationServer, RefreshTokenRepositoryInterface $refreshTokenRepository, UserVerificationService $userVerificationService)
    {
        $this->authorizationServer = $authorizationServer;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->userVerificationService = $userVerificationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['extendOAuthSetup', 127],
            ]
        ];
    }

    public function extendOAuthSetup(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->authorizationServer->enableGrantType(new WebAuthnGrant(
            $this->refreshTokenRepository,
            $this->userVerificationService
        ));
    }
}
