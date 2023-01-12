<?php declare(strict_types=1);

namespace App\Provider;

use App\Renderer\TwoFactorRenderer;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\TwoFactorProviderLogicException;

final class TwoFactorProvider implements TwoFactorProviderInterface
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly TwoFactorRenderer $formRenderer
    )
    {
    }

    public function beginAuthentication(
        AuthenticationContextInterface $context
    ): bool
    {
        $user = $context->getUser();

        if (!($user instanceof TwoFactorInterface && $user->isGoogleAuthenticatorEnabled())) {
            return false;
        }

        $secret = $user->getGoogleAuthenticatorSecret();

        if (is_null($secret) || strlen($secret) === 0) {
            throw new TwoFactorProviderLogicException('User has to provide a secret code for Google Authenticator authentication.');
        }

        return true;
    }

    public function prepareAuthentication(
        object $user
    ): void
    {
    }

    public function validateAuthenticationCode(
        object $user,
        string $authenticationCode
    ): bool
    {
        if (!$user instanceof TwoFactorInterface) {
            return false;
        }

        return $this->googleAuthenticator->checkCode($user, $authenticationCode);
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}
