<?php declare(strict_types=1);

namespace App\Service\Website\Dashboard;

use Symfony\Component\Security\Core\User\UserInterface;

final class DashboardService
{
    private array $parameter;

    public function addParameter(array $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function addDefaultParameter(
        UserInterface $user
    ): self
    {
        $this->parameter = [
            'nickname' => $user->getNickname(),
        ];

        return $this;
    }

    public function getParameter(): array
    {
        return $this->parameter;
    }
}
