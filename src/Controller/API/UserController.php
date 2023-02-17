<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\API\UserService;
use App\Serializer\UserNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserNormalizer $userNormalizer,
        private readonly UserService $userService
    )
    {
    }

    /**
     * @Route("/api/user/{uuid}", name="api_user_by_uuid", methods={"GET"})
     */
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
