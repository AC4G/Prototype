<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Repository\UserRepository;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIUserListenerService
{
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly UserRepository $userRepository
    )
    {
    }

    public function validateJWTAndParameterForUserController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $event->getRequest()->attributes->set('scopes', $accessToken['scopes']);

        $uuid = $params['uuid'];

        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if (!$this->securityService->hasClientPermissionForUserRelatedDataAction($accessToken, $user, $event->getRequest())) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }


}
