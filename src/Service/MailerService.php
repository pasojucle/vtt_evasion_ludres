<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Service\MailerResult;
use App\Entity\User;
use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private ParameterService $parameterService,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    private function getClubAndWebmasterEmails(): array
    {
        return [
            new Address($this->parameterBag->get('club_email')),
            new Address($this->parameterBag->get('webmaster_email'))
        ];
    }

    public function sendMailToClub(array $data): bool
    {
        [$clubEmail, $webmasterEmail] = $this->getClubAndWebmasterEmails();

        try {
            $replyTo = new Address($data['email']);
        } catch (Exception) {
            return false;
        }
        
        $email = (new TemplatedEmail())
            ->to($clubEmail)
            ->replyTo($replyTo)
            ->subject($data['subject'])
            ->htmlTemplate('email/toClub.html.twig')
            ->context([
                'data' => $data,
            ])
        ;

        if ($this->parameterService->getParameterByName('DEDUPLICATION_MAILER_ENABLED') || array_key_exists('error', $data)) {
            $email->addBcc($webmasterEmail);
        }

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendMailToMember(
        string $userEmail,
        string $fullName,
        string $subject,
        string $content,
        ?array $attachements = null,
    ): MailerResult {
        [$clubEmail, $webmasterEmail] = $this->getClubAndWebmasterEmails();

        if (true === $this->parameterService->getParameterByName('TEST_MODE')) {
            $userEmail = $clubEmail->getAddress();
        }

        try {
            $email = new Address($userEmail);
        } catch (Exception) {
            return MailerResult::failure('Adresse mail manquante ou erronnée');
        }

        $email = (new TemplatedEmail())
            ->to($email)
            ->replyTo($clubEmail)
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
            $email->addBcc($webmasterEmail);
        }

        try {
            $this->mailer->send($email);

            return MailerResult::success();
        } catch (TransportExceptionInterface $e) {
            return MailerResult::failure('Problème d\'envoi de mail');
        }
    }

    public function sendMailToParticipant(User $participant, string $subject, string $content, ?array $attachements = null): MailerResult
    {
        $particpantEmail = $participant->getContactEmail();
        [$clubEmail, $webmasterEmail] = $this->getClubAndWebmasterEmails();
        if (true === $this->parameterService->getParameterByName('TEST_MODE')) {
            $particpantEmail = $clubEmail->getAddress();
        }

        try {
            $email = new Address($particpantEmail);
        } catch (Exception $e) {
            return MailerResult::failure('Adresse mail manquante ou erronnée');
        }

        $email = (new TemplatedEmail())
            ->to($email)
            ->replyTo($clubEmail)
            ->subject($subject)
            ->htmlTemplate('email/toParticipant.html.twig')
            ->context([
                'subject' => $subject,
                'content' => $content,
            ])
        ;
        if ($attachements) {
            foreach ($attachements as $attachement) {
                $email->addPart(new DataPart(new File($attachement)));
            }
        }
        if ($this->parameterService->getParameterByName('DEDUPLICATION_MAILER_ENABLED')) {
            $email->addBcc($webmasterEmail);
        }

        try {
            $this->mailer->send($email);

            return MailerResult::success();
        } catch (TransportExceptionInterface $e) {
            return MailerResult::failure('Problème d\'envoi de mail');
        }
    }
}
