<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\UserRepository;
use App\Service\API\Chat\ChatService;
use App\Repository\ChatRoomRepository;
use App\Serializer\Chat\ChatRoomNormalizer;
use App\Repository\ChatRoomMemberRepository;
use App\Service\Response\API\CustomResponse;
use App\Repository\ChatRoomMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ChatController extends AbstractController
{

}
