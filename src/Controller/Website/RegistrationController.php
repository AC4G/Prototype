<?php declare(strict_types=1);

namespace App\Controller\Website;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
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
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private RegistrationService $registrationService,
        private UserRepository $userRepository,
        private Security $security
    )
    {
    }

    /**
     * @Route("/registration", name="registration", methods={"GET", "POST"})
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
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

    /**
     * @Route("/verify", name="verify_email")
     */
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
