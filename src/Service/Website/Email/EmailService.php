<?php

namespace App\Service\Website\Email;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use function PHPUnit\Framework\throwException;

class EmailService
{
    private TemplatedEmail $email;

    public function __construct(
        private readonly MailerInterface $mailer
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
            throwException(new Exception($e->getMessage()));
        }
    }


}
