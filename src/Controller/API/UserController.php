<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Serializer\UserNormalizer;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserNormalizer $userNormalizer
    )
    {
    }

    #[Route('/api/user/{uuid}', name: 'api_user_by_uuid', methods: [Request::METHOD_GET])]
    public function getUserInformationByUuid(
        string $uuid
    ): Response
    {
        $user = $this->userNormalizer->normalize($this->userRepository->getUserByUuidFromCache($uuid), null, 'user_api');

        return new JsonResponse(
            $user
        );
    }

}
