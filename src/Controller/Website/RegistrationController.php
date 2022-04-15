<?php declare(strict_types=1);

namespace App\Controller\Website;

use DateTime;
use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Website\Email\EmailService;
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
        private EmailService $emailService,
        private Security $security
    )
    {
    }

    /**
     * @Route("/registration", name="registration", methods={"GET", "POST"})
     * @throws Exception
     */
    public function index(
        Request $request
    ): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $errors = [];

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        $agreeTerms = $form->get('agreeTerms')->getData();

        if ($form->isSubmitted() && $form->isValid() && $agreeTerms) {
            $data = $request->request->all('registration_form');

            $user
                ->setNickname($data['nickname'])
                ->setEmail($data['email'])
                ->setPassword($data['password']['first'])
            ;

            if($this->userRepository->isNicknameAlreadyUsed($user->getNickname())) {
                $errors[] =  "Nickname with the same characters is already in use.";
            }

            if ($this->userRepository->isEmailAlreadyUsed($user->getEmail())) {
                $errors[] = 'An account with this email already exists.';
            }

            if (count($errors) < 1) {
                $this->registrationService->registerUser($user);
                $errors = $this->registrationService->getErrors();
            }

            if(count($errors) < 1) {
                $response = $this->redirectToRoute('login');

                $userId = $this->registrationService->getUser()->getId();

                $signatureComponents = $this->verifyEmailHelper->generateSignature(
                    'verify_email',
                    (string)$userId,
                    $user->getEmail(),
                    ['id' => $userId]
                );

                $this->emailService->createEmail($user->getEmail(), 'Email verification', 'website/email/registration/index.html.twig', [
                    'verify_url' => $signatureComponents->getSignedUrl(),
                ]);

                $this->emailService->sendEmail();

                $errors = $this->emailService->getError();

                if (array_key_exists('email', $errors)) {
                    $this->userRepository->deleteEntry($user);
                }

                if (!array_key_exists('email', $errors)) {
                    $this->registrationService->giveUserARole($this->registrationService->getUser());

                    /*
                     *   only for debugging purpose <4/2/2022 6:35PM AC4G>
                     *   $errors = $this->registrationService->getErrors();
                    */
                }

                if (count($errors) < 1) {
                    $this->addFlash('success', 'Please follow the link in your Email to verify it.');

                    return $response;
                }
            }
        }

        return $this->render('website/registration/index.html.twig', [
            'registration_form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/verify", name="verify_email")
     */
    public function verifyEmail(
        Request $request
    )
    {
        $user = $this->userRepository->find($request->query->get('id'));

        if (is_null($user)) {
            $this->addFlash('success', 'You are already verified!');

            return $this->redirectToRoute('login');
        }

        $emailVerified = $user->getEmailVerified();

        if (!is_null($emailVerified)) {
            $this->redirectToRoute('login');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                (string)$user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());

            return $this->redirectToRoute('login');
        }

        $user->setEmailVerified(new DateTime());
        $this->userRepository->flushEntity();

        $this->addFlash('success', 'Email verified! You can now log in to your account.');

        return $this->redirectToRoute('login');
    }
}