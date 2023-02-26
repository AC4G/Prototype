<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Serializer\PublicKeyNormalizer;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\API\PublicKey\PublicKeyService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/publicKey/{uuid}', name: 'api_public_key_by_uuid_')]
final class PublicKeyController extends AbstractController
{
    public function __construct(
        private readonly PublicKeyNormalizer $publicKeyNormalizer,
        private readonly PublicKeyService $publicKeyService,
        private readonly CustomResponse $customResponse
    )
    {
    }

    #[Route('', name: "get", methods: [Request::METHOD_GET])]
    public function getPublicKey(
        string $uuid
    ): Response
    {
        $publicKey = $this->publicKeyService->getPublicKeyByUuidFromCache($uuid);

        return new JsonResponse($this->publicKeyNormalizer->normalize($publicKey));
    }

    #[Route('', name: 'post', methods: [Request::METHOD_POST])]
    public function postPublicKey(
        Request $request,
        string $uuid
    ): Response
    {
        $key = json_decode($request->getContent(), true)['key'];
        $this->publicKeyService->savePublicKey($uuid, $key);

        return  $this->customResponse->notificationResponse($request, 'Public successfully saved');
    }

    #[Route('', name: 'patch', methods: [Request::METHOD_PATCH])]
    public function patchPublicKey(
        Request $request,
        string $uuid
    ): Response
    {
        $key = json_decode($request->getContent(), true)['key'];
        $this->publicKeyService->updatePublicKey($uuid, $key);

        return $this->customResponse->notificationResponse($request, 'Public successfully updated');
    }

    #[Route('', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function deletePublicKey(
        Request $request,
        string $uuid
    ): Response
    {
        $this->publicKeyService->deletePublicKey($uuid);

        return $this->customResponse->notificationResponse($request, 'Public key successfully deleted');
    }


}
