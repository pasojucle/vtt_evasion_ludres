<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use App\Repository\ParameterRepository;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private ReplaceKeywordsService $replaceKeywords,
    ) {
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

    public function sendMailToMember(array|UserDto $user, string $subject, string $content, string $attachement = null): array
    {
        list($userEmail, $fullName) = $this->getUserData($user);
        if ($user instanceof UserDto) {
            $content = $this->replaceKeywords->replace($user, $content, RegistrationStep::RENDER_FILE);
        }

        try {
            $email = new Address($userEmail);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Adresse mail manquante ou erronnée',
            ];
        }

        $email = (new TemplatedEmail())
            ->to($email)
            ->subject($subject)
            ->htmlTemplate('email/toMember.html.twig')
            ->context([
                'subject' => $subject,
                'fullName' => $fullName,
                'content' => $content,
            ])
        ;
        if ($attachement) {
            $email
                ->addPart(new DataPart(new File($attachement)));
        }

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

    private function getUserData(array|UserDto $user): array
    {
        if ($user instanceof UserDto) {
            return [$user->mainEmail, $user->member->fullName];
        }
        return [$user['email'], sprintf('%s %s', $user['name'], $user['firstName'])];
    }
}
