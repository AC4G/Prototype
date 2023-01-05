<?php declare(strict_types=1);

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginListener
{
    use TargetPathTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function onSecurityInteractiveLogin(
        InteractiveLoginEvent $event
    )
    {
        $user = $event->getAuthenticationToken()->getUser();
        $previousPage = $this->getTargetPath($event->getRequest()->getSession(), 'main');

        if ($user->isTwoFaVerified() && !is_null($previousPage)) {
            $event->getRequest()->getSession()->set('redirect', $previousPage);
        }
    }


}