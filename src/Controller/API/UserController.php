<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Serializer\UserNormalizer;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserNormalizer $userNormalizer,
        private readonly CacheInterface $cache
    )
    {
    }

    /**
     * @Route("/api/user/{uuid}", name="api_user_by_uuid", methods={"GET"})
     */
    public function getUserInformation(
        Request $request,
        string $uuid
    ): Response
    {
        $user = $this->userNormalizer->normalize($this->cache->getItem('user_' . $uuid)->get(), null, 'user_api');

        return new Response(
            json_encode($user),
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

}
