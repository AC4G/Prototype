<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final class SecurityService
{
    public function __construct(
        private UserRepository $userRepository
    )
    {
    }

    public function getUserByCredentials(
        Request $request
    ): ?User
    {
        $data = $request->request->all('login_form');

        $user = $this->userRepository->findOneBy(['nickname' => $data['nickname']]);

        if (!is_null($user) && password_verify($data['password'], $user->getPassword())) {
            return $user;
        }

        return null;
    }


}