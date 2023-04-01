<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\FileRepository;
use App\Repository\UserRepository;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FileController extends AbstractController
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly CustomResponse $customResponse,
        private readonly UserRepository $userRepository
    )
    {
    }

    #[Route('/files/profile/{uuid}', name: 'profile_picture_by_uuid')]
    public function getProfilePictureByUuid(
        Request $request,
        string $uuid
    ): Response
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        if (is_null($user) || is_null($user->getProfilePic())) {
            return $this->customResponse->errorResponse($request, 'Profile picture not found!', 404);
        }

        $pic = $this->fileRepository->getProfilePictureByUser($user);

        if (is_null($pic)) {
            return $this->customResponse->errorResponse($request, 'Profile picture not found!', 404);
        }

        $finfo = finfo_open();
        $type = finfo_buffer($finfo, $pic, FILEINFO_MIME_TYPE);
        finfo_close($finfo);

        return new Response($pic, 200, [
            'Content-Type' => $type
        ]);
    }


}
