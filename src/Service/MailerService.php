<?php

namespace App\Service;

use App\Repository\ParameterRepository;
use Exception;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailerService
{
    private MailerInterface $mailer;
    private ParameterRepository $parameterRepository;
    
    public function __construct(MailerInterface $mailer, ParameterRepository $parameterRepository)
    {
        $this->mailer = $mailer;
        $this->parameterRepository = $parameterRepository;
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

    public function sendMailToMember(array $data, ?string $paramName = null): bool
    {
        $parameter = null;
        $content = null;
        if (null !== $paramName) {
            $parameter = $this->parameterRepository->findOneByName($paramName);
        }
        if (null !== $parameter) {
            $content = $parameter->getValue();
        }
        if (array_key_exists('content', $data)) {
            $content = $data['content'];
        }

        $email = (new TemplatedEmail())
            ->to(new Address($data['email']))
            ->subject($data['subject'])
            ->htmlTemplate('email/toMember.html.twig')
            ->context([
                'data' => $data,
                'content' => $content,
            ]);
        
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }


    public function sendError(array $error): bool
    {
    
        $email = (new TemplatedEmail())
            ->to(new Address('contact@vttevasionludres.fr'))
            ->subject('[ERREUR] Site vttevasionludres')
            ->htmlTemplate('email/error.html.twig')
            ->context([
                'error' => $error,
            ]);
        
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}