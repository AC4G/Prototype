<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Repository\RoleIdentRepository;
use App\Repository\UserRolesRepository;
use DateTime;
use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Cookie\CookieService;
use App\Service\Website\Email\EmailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Registration\RegistrationFormType;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Registration\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\TokenRepository as KeyRepository;

class RegistrationController extends AbstractController
{
    public function __construct(
        private RegistrationService $registrationService,
        private UserRepository $userRepository,
        private EmailService $emailService
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
                $expire = new DateTime('+ 30 days');

                $response = $this->redirectToRoute('login');

                $this->emailService->createEmail($user->getEmail(), 'Email verification', 'website/email/registration/index.html.twig', [
                    'userId' => $this->registrationService->getUser()->getId(),
                    'verification_key' => $this->registrationService->getUserRegistrationKey()->getKey(),
                ]);

                $this->emailService->sendEmail();

                $errors = $this->emailService->getError();

                if (array_key_exists('email', $errors)) {
                    $this->userRepository->deleteEntry($user);
                }

                if (!array_key_exists('email', $errors)) {
                    $this->registrationService->giveUserARole($this->registrationService->getUser());
                    $this->registrationService->flushUserRegistrationKey();

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
     * @Route("/registration/success", name="registration_success", methods={"GET"})
     */
    public function registrationSuccess(
        Request $request
    ): Response
    {
        if (!$request->cookies->has('userId')) {
            return $this->redirectToRoute('login');
        }

        $user = $this->userRepository->findOneBy(['id' => (int)$request->cookies->get('userId')]);

        if (is_null($user)) {
            return $this->redirectToRoute('login');
        }

        $verified = $user->getEmailVerified();

        if (!is_null($verified)) {
            $response = $this->redirectToRoute('login');
            $response->headers->clearCookie('userId');

            return $response;
        }

        return $this->render('website/registration/success.html.twig');
    }
}