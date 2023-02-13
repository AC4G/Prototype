<?php

namespace App\Renderer;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;

final class TwoFactorRenderer implements TwoFactorFormRendererInterface
{
    public function __construct(
        private readonly Environment $twigEnvironment,
        private readonly FirewallMapInterface $firewallMap
    ) {
    }

    public function renderForm(Request $request, array $templateVars): Response
    {
        $templates = [
            'main' => 'website/security/2fa_form.html.twig'
        ];
        $firewallName = $this->firewallMap->getFirewallConfig($request)->getName();
        $template = $templates[$firewallName];

        $content = $this->twigEnvironment->render($template, $templateVars);
        $response = new Response();
        $response->setContent($content);

        return $response;
    }
}
