<?php

namespace App\EventSubscriber;

use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public function onCheckPassport(
        CheckPassportEvent $event
    )
    {
        $passport = $event->getPassport();

        $user = $passport->getUser();

        if (is_null($user->getEmailVerified())) {
            throw new  CustomUserMessageAuthenticationException(
                'Pleas verify your email first before logging in.'
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
        ];
    }
}