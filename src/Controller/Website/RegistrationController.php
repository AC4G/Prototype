<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Registration\RegistrationFormType;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Registration\RegistrationService;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly RegistrationService $registrationService,
        private readonly UserRepository $userRepository
    )
    {
    }

    #[Route('/registration', name: 'registration_get', methods: [Request::METHOD_GET])]
    public function getRegistration(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        return $this->renderForm('website/registration/index.html.twig', [
            'registration_form' => $form,
            'errors' => [],
        ]);
    }

    #[Route('/registration', name: 'registration_post', methods: [Request::METHOD_POST])]
    public function postRegistration(
        Request $request
    ): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid() || !$form->get('agreeTerms')->getData()) {
            return $this->renderForm('website/registration/index.html.twig', [
                'registration_form' => $form,
                'errors' => [],
            ]);
        }

        $data = $request->request->all('registration_form');

        $errors = $this->registrationService->getValidationErrors($data);

        if (count($errors) !== 0) {
            return $this->renderForm('website/registration/index.html.twig', [
                'registration_form' => $form,
                'errors' => $errors,
            ]);
        }

        $this->registrationService->registerUser($user, $data);

        $this->addFlash('success', 'Please follow the link in your Email to verify it.');

        return $this->redirectToRoute('login');
    }

    #[Route('/verify', name: 'verify_email', methods: [Request::METHOD_GET])]
    public function verifyEmail(Request $request): Response
    {
        $userId = $request->query->get('id');
        $user = $this->userRepository->find($userId);

        if (is_null($user)) {
            $this->addFlash('success', 'You are already verified!');
            return $this->redirectToRoute('login');
        }

        if (!is_null($user->getEmailVerified())) {
            return $this->redirectToRoute('login');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                (string)$userId,
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('login');
        }

        $this->registrationService->setEmailVerifiedDate($user);

        $this->addFlash('success', 'Email verified! You can now log in to your account.');

        return $this->redirectToRoute('login');
    }


}
