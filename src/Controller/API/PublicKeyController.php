<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Serializer\PublicKeyNormalizer;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\API\PublicKey\PublicKeyService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PublicKeyController extends AbstractController
{
    public function __construct(
        private readonly PublicKeyNormalizer $publicKeyNormalizer,
        private readonly PublicKeyService $publicKeyService,
        private readonly CustomResponse $customResponse,
        private readonly CacheInterface $cache
    )
    {
    }

    /**
     * @Route("/api/publicKey/{uuid}", name="api_public_key_by_uuid", methods={"GET", "POST", "PATCH", "DELETE"})
     */
    public function getPublicKeyAction(
        Request $request,
        string $uuid
    ): Response
    {
        if ($request->isMethod('GET')) {
            $publicKey = $this->publicKeyService->getPublicKeyByUuidFromCache($uuid);

            return new JsonResponse($this->publicKeyNormalizer->normalize($publicKey));
        }

        $this->cache->delete('public_key_' . $uuid);

        if ($request->isMethod('DELETE')) {
            $this->publicKeyService->deletePublicKey($uuid);

            return $this->customResponse->notificationResponse($request, 'Public key successfully deleted');
        }

        $key = json_decode($request->getContent(), true)['key'];

        if ($request->isMethod('POST')) {
            $this->publicKeyService->savePublicKey($uuid, $key);

            return  $this->customResponse->notificationResponse($request, 'Public successfully saved');
        }

        $this->publicKeyService->updatePublicKey($uuid, $key);

        return $this->customResponse->notificationResponse($request, 'Public successfully updated');
    }


}
