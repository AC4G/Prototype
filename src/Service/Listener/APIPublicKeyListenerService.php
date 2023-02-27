<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Repository\PublicKeyRepository;
use App\Service\API\Security\SecurityService;
use App\Service\Response\API\CustomResponse;
use App\Service\UserService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class APIPublicKeyListenerService
{
    public function __construct(
        private readonly PublicKeyRepository $publicKeyRepository,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly UserService $userService,
        private readonly CacheInterface $cache
    )
    {
    }

    public function validateJWTForPublicKeyController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $uuid = $params['uuid'];

        $user = $this->userService->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if ($event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAccessingUserRelatedData($accessToken, $user) || $accessToken['project']['id'] !== 1) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        $publicKey = $this->cache->get('public_key_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->publicKeyRepository->findOneBy(['user' => $user]);
        });

        if (is_null($publicKey) && !$event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Public key not found', 404));

            return;
        }

        if (!is_null($publicKey) && $event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s has already a public key', $uuid), 400));

            return;
        }

        if ($event->getRequest()->isMethod('POST') || $event->getRequest()->isMethod('PATCH')) {
            $content = json_decode($event->getRequest()->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Invalid Json!', 406));

                return;
            }

            if (!array_key_exists('key', $content)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Key not provided!', 406));

                return;
            }

            if (preg_match('/^ssh-rsa AAAA[0-9A-Za-z+\/]+[=]{0,3}( [^@]+@[^@]+)?$/', $content['key']) === 0) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Key is not provided in OpenSSH format!', 406));

                return;
            }
        }

        if (!$event->getRequest()->isMethod('GET')) {
            $this->cache->delete('public_key_' . $uuid);
        }
    }

}
