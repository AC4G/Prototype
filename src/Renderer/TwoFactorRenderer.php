<?php

namespace App\Renderer;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;

final class TwoFactorRenderer implements TwoFactorFormRendererInterface
{
    public function __construct(
        private readonly Environment $twigEnvironment,
    )
    {
    }

    public function renderForm(Request $request, array $templateVars): Response
    {
        $templates = [
            'main' => 'website/security/2fa_form.html.twig'
        ];

        //on firewall main
        $firewallName = 'main';
        $template = $templates[$firewallName];

        $content = $this->twigEnvironment->render($template, $templateVars);
        $response = new Response();
        $response->setContent($content);

        return $response;
    }
}
