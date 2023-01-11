<?php declare(strict_types=1);

namespace App\Controller\Website;

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
        private readonly ResetPasswordService $resetPasswordService
    )
    {
    }

    /**
     * @Route("/passwordForgotten", name="password_forgotten", methods={"GET", "POST"})
     */
    public function preparePwdForgottenForVerification(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(EmailFormType::class);

        if (!$request->isMethod('POST')) {
            return $this->renderForm('website/resetPassword/email.html.twig', [
                'error' => null,
                'form' => $form
            ]);
        }

        $form->handleRequest($request);
        $error = $this->resetPasswordService->validateEmail($form->getData());

        if (!$form->isSubmitted() || !$form->isValid() || !is_null($error)) {
            return $this->renderForm('website/resetPassword/email.html.twig', [
                'error' => $error,
                'form' => $form
            ]);
        }

        $this->resetPasswordService->prepareForReset($request);

        return $this->redirectToRoute('password_forgotten_verify');
    }

    /**
     * @Route("/passwordForgotten/verify", name="password_forgotten_verify", methods={"GET", "POST"})
     */
    public function verifyPwdForgotten(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $email = $request->getSession()->get('reset_password_email');

        if (is_null($email)) {
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(CodeFormType::class);

        if (!$request->isMethod('POST')) {
            return $this->renderForm('website/resetPassword/code.html.twig', [
                'form' => $form,
                'error' => null
            ]);
        }

        $form->handleRequest($request);
        $code = array_key_exists('code', $form->getData()) ? $form->getData()['code'] : null;
        $error = $this->resetPasswordService->validateCode($request, $code);

        if (!$form->isSubmitted() || !$form->isValid() || !is_null($error)) {
            return $this->renderForm('website/resetPassword/code.html.twig', [
                'form' => $form,
                'error' => $error
            ]);
        }

        $this->resetPasswordService->setSessionIsVerifiedForResetPassword($request);
        $this->resetPasswordService->removeEntry();

        return $this->redirectToRoute('password_forgotten_reset');
    }

    /**
     * @Route("/passwordForgotten/reset", name="password_forgotten_reset", methods={"GET", "POST"})
     */
    public function resetPassword(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $isVerified = $request->getSession()->get('is_verified_for_reset_password');

        if ($isVerified !== true) {
            return $this->redirectToRoute('login');
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