<?php

namespace App\Renderer;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;

class TwoFactorRenderer implements TwoFactorFormRendererInterface
{
    public function __construct(
        private Environment $twigEnvironment
    ) {
    }

    public function renderForm(Request $request, array $templateVars): Response
    {
        $template = 'website/security/2fa_form.html.twig';

        $content = $this->twigEnvironment->render($template, $templateVars);
        $response = new Response();
        $response->setContent($content);

        return $response;
    }
}