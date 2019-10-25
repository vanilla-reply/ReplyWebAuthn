<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractController extends StorefrontController
{
    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->container->get('session');
    }

    /**
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function createErrorResponse(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
}
