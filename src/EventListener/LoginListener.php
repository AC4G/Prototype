<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class LoginListener
{
    use TargetPathTrait;

    public function onSecurityInteractiveLogin(
        InteractiveLoginEvent $event
    ): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user->isTwoFaVerified()) {
            $event->getRequest()->getSession()->set('redirectLogin', $event->getRequest()->getRequestUri());
        }
    }


}
