<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDto;
use App\Entity\RegistrationStep;
use Error;
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
        private ParameterService $parameterService,
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

        if ($this->parameterService->getParameterByName('DEDUPLICATION_MAILER_ENABLED')) {
            $email->addBcc(new Address('pasojucle@gmail.com'));
        }

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendMailToMember(array|UserDto $user, string $subject, string $content, ?array $attachements = null, ?array $additionalParams = []): array
    {
        list($userEmail, $fullName) = $this->getUserData($user);
        if (true === $this->parameterService->getParameterByName('TEST_MODE')) {
            $userEmail = 'contact@vttevasionludres.fr';
        }

        if ($user instanceof UserDto) {
            $content = $this->replaceKeywords->replace($content, $user, RegistrationStep::RENDER_FILE, $additionalParams);
        }

        $error = [
            'success' => false,
            'message' => 'Adresse mail manquante ou erronnée',
        ];
        try {
            $email = new Address($userEmail);
        } catch (Exception) {
            return $error;
        } catch (Error) {
            return $error;
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
        if ($attachements) {
            foreach ($attachements as $attachement) {
                $email->addPart(new DataPart(new File($attachement)));
            }
        }

        if ($this->parameterService->getParameterByName('DEDUPLICATION_MAILER_ENABLED')) {
            $email->addBcc(new Address('pasojucle@gmail.com'));
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
            return [$user->mainEmail, $user->mainFullName];
        }
        return [$user['email'], sprintf('%s %s', $user['name'], $user['firstName'])];
    }
}
