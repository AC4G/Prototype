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

final class PublicKeyController extends AbstractController
{
    public function __construct(
        private readonly PublicKeyNormalizer $publicKeyNormalizer,
        private readonly PublicKeyService $publicKeyService,
        private readonly CustomResponse $customResponse
    )
    {
    }

    /**
     * @Route("/api/publicKey/{uuid}", name="api_public_key_by_uuid", methods={"GET", "POST", "PATCH"})
     */
    public function getPublicKeyAction(
        Request $request,
        string $uuid
    ): Response
    {
        $publicKey = $this->publicKeyService->getPublicKeyByUuidFromCache($uuid);

        if ($request->isMethod('GET')) {
            return new JsonResponse($this->publicKeyNormalizer->normalize($publicKey));
        }

        $content = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        //todo: implement creation and updating public for official stexs client
        return new Response();
    }


}
