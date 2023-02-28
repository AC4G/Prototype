<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Serializer\UserNormalizer;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserNormalizer $userNormalizer,
        private readonly UserService $userService
    )
    {
    }

    #[Route('/api/user/{uuid}', name: 'api_user_by_uuid', methods: [Request::METHOD_GET])]
    public function getUserInformation(
        string $uuid
    ): Response
    {
        $user = $this->userNormalizer->normalize($this->userService->getUserByUuidFromCache($uuid), null, 'user_api');

        return new JsonResponse(
            $user
        );
    }

}
