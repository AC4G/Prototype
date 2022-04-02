<?php

namespace App\Service\Website\Email;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    private TemplatedEmail $email;
    private array $error = [];

    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function createEmail(
        string $goal,
        string $subject,
        string $template,
        array $context
    ): void
    {
        $this->email = (new TemplatedEmail())
            ->to($goal)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context)
        ;
    }

    public function sendEmail(): void
    {
        try {
            $this->mailer->send($this->email);
        } catch (Exception $e) {
            $this->error['email'] = 'Error sending email. Try it one more time. Message: ' . $e->getMessage();
        }
    }

    public function getError(): array
    {
        return $this->error;
    }
}