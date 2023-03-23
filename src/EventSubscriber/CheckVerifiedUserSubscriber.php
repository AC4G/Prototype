<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\Website\Account\AccountService;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AccountService $accountService
    )
    {
    }

    public function onCheckPassport(
        CheckPassportEvent $event
    )
    {
        $passport = $event->getPassport();

        /** @var User $user */
        $user = $passport->getUser();

        if (is_null($user->getEmailVerified())) {
            throw new  CustomUserMessageAuthenticationException(
                'Pleas verify your email first before logging in.'
            );
        }

        if (!is_null($user->getGoogleAuthenticatorSecret()) && strlen($user->getGoogleAuthenticatorSecret()) > 0 && is_null($user->getTwoFaVerified())) {
            $this->accountService->removeTwoFaOneTimeTokens($user);
            $this->accountService->unsetTwoStepVerification($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
        ];
    }
}
