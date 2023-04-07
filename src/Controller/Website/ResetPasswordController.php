<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Service\ThrottlingService;
use App\Form\ResetPassword\CodeFormType;
use App\Form\ResetPassword\EmailFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ResetPassword\ResetPasswordFormType;
use App\Service\Website\Security\ResetPasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly ResetPasswordService $resetPasswordService,
        private readonly ThrottlingService $throttlingService
    )
    {
    }

    #[Route('/passwordForgotten', name: 'password_forgotten', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function preparePwdForgottenForVerification(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $email = $request->query->get('newCode');

        if (!is_null($email) && str_contains($this->resetPasswordService->validateEmail(['email' => urldecode($email)]), 'Reset code already sent to this Email')) {
            $this->resetPasswordService->updateEntrySendEmailAndSetSession($request);

            return $this->redirectToRoute('password_forgotten_verify');
        }

        $form = $this->createForm(EmailFormType::class);

        if (!$request->isMethod('POST')) {
            return $this->renderForm('website/resetPassword/email.html.twig', [
                'error' => null,
                'form' => $form,
                'email' => null
            ]);
        }

        $form->handleRequest($request);
        $error = $this->resetPasswordService->validateEmail($form->getData());

        if (!$form->isSubmitted() || !$form->isValid() || !is_null($error)) {
            return $this->renderForm('website/resetPassword/email.html.twig', [
                'error' => $error,
                'form' => $form,
                'email' => !is_null($form->getData()['email']) && str_contains($error, 'Reset code already sent') ? urlencode($form->getData()['email']) : null
            ]);
        }

        $this->resetPasswordService->prepareForReset($request);

        return $this->redirectToRoute('password_forgotten_verify');
    }

    #[Route('/passwordForgotten/verify', name: 'password_forgotten_verify', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function verifyPwdForgotten(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $email = $request->getSession()->get('reset_password_email');

        if (is_null($email)) {
            return $this->redirectToRoute('password_forgotten');
        }

        $form = $this->createForm(CodeFormType::class);

        $newCode = (bool)$request->query->get('newCode');

        if ($newCode === true && !$request->isMethod('POST')) {
            $entryExists = $this->resetPasswordService->entryExists($email);

            if ($entryExists === false) {
                $this->addFlash('error', 'Retype your email again!');

                return $this->redirectToRoute('password_forgotten');
            }

            $this->resetPasswordService->setResetByUser();
            $this->resetPasswordService->updateEntryAndSendEmail();
        }

        if (!$request->isMethod('POST') || $newCode === true) {
            return $this->renderForm('website/resetPassword/code.html.twig', [
                'form' => $form,
                'error' => null
            ]);
        }

        $form->handleRequest($request);
        $code = array_key_exists('code', $form->getData()) ? $form->getData()['code'] : null;
        $error = $this->resetPasswordService->validateCode($request, $code);
        $throttling = $this->throttlingService->setup(
            $request->getClientIp(),
            10,
            600
        );


        if (!is_null($error)) {
            if ($throttling->hasClientAttemptsLeft()) {
                $throttling->increaseCounter();
            }

            if (!$throttling->hasClientAttemptsLeft()) {
                $error = 'Too many attempts, retry in ' . $throttling->getStaticWaitTime() . ' minutes.';
            }
        }

        if (!$form->isSubmitted() || !$form->isValid() || !is_null($error)) {
            return $this->renderForm('website/resetPassword/code.html.twig', [
                'form' => $form,
                'error' => $error
            ]);
        }

        $throttling->remove();

        $this->resetPasswordService->setSessionIsVerifiedForResetPassword($request);
        $this->resetPasswordService->removeEntry();

        return $this->redirectToRoute('password_forgotten_reset');
    }

    #[Route('/passwordForgotten/reset', name: 'password_forgotten_reset', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function resetPassword(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $isVerified = $request->getSession()->get('is_verified_for_reset_password');

        if ($isVerified !== true) {
            return $this->redirectToRoute('password_forgotten');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $email = $request->getSession()->get('reset_password_email');

        if (!$request->isMethod('POST')) {
            return $this->renderForm('website/resetPassword/reset.html.twig', [
                'form' => $form,
                'email' => $email,
                'error' => null
            ]);
        }

        $form->handleRequest($request);
        $error = $this->resetPasswordService->validatePassword($form->getData(), $email);

        if (!$form->isSubmitted() || !$form->isValid() || !is_null($error)) {
            return $this->renderForm('website/resetPassword/reset.html.twig', [
                'form' => $form,
                'email' => $email,
                'error' => $error
            ]);
        }

        $this->resetPasswordService->saveNewPassword();
        $this->resetPasswordService->removeSessionsForResettingPassword($request);

        $this->addFlash('success', 'Password successfully changed!');

        return $this->redirectToRoute('login');
    }


}
