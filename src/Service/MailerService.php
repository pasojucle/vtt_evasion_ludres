<?php

namespace App\Service;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
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

    public function sendMailContact(array $data)
    {
        $user = 'contact.blng.fr';
        $pass = 'Xugk33b+a';
        $server = 'ssl0.ovh.net';
        $port = '465';

        // Generate connection configuration
        $dsn = "smtp://" . $user . ":" . $pass . "@" . $server . ":" . $port;
        $transport = Transport::fromDsn($dsn);
        $customMailer = new Mailer($transport);
        $email = (new TemplatedEmail())
            ->to(new Address('contact@vttevasionludres.fr'))
            ->subject('Message envoyé depuis le site vttevasionludres.fr')
            ->htmlTemplate('email/contact.html.twig')
            ->context([
                'data' => $data,
            ]);
            $loader = new FilesystemLoader('../templates/');
            $twigEnv = new Environment($loader);
            $twigBodyRenderer = new BodyRenderer($twigEnv);
            $twigBodyRenderer->render($email);
        
        try {
            return $customMailer->send($email);

        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}