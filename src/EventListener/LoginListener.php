<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    use TargetPathTrait;

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