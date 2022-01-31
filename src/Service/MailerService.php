<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ParameterRepository;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

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
            ])
        ;

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendMailToMember(array $data, ?string $paramName = null): array
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

        try {
            $email = new Address($data['email']);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Adresse mail manquante ou erronnée',
            ];
        }

        $email = (new TemplatedEmail())
            ->to(new Address($data['email']))
            ->subject($data['subject'])
            ->htmlTemplate('email/toMember.html.twig')
            ->context([
                'data' => $data,
                'content' => $content,
            ])
        ;

        try {
            $this->mailer->send($email);

            return [
                'success' => true,
            ];
        } catch (TransportExceptionInterface $e) {
            return [
                'success' => false,
                'message' => 'Problème d\'envoi de mail',
            ];
        }
    }
}
