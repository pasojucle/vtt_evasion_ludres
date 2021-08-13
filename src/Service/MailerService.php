<?php

namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerService
{
    private MailerInterface $mailer;
    
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMailToClub(array $data): bool
    {
    
        $email = (new TemplatedEmail())
            ->to(new Address('contact@vttevasionludres.fr'))
            ->subject($data['subject'])
            ->htmlTemplate('email/toClub.html.twig')
            ->context([
                'data' => $data,
            ]);
        
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendMailToMember(array $data): bool
    {
    
        $email = (new TemplatedEmail())
            ->to(new Address($data['email']))
            ->subject($data['subject'])
            ->htmlTemplate('email/toMember.html.twig')
            ->context([
                'data' => $data,
            ]);
        
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}