<?php

declare(strict_types=1);

namespace App\UseCase\Guest;

use App\Entity\BikeRide;
use App\Entity\Guest;
use App\Repository\GuestRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ProjectDirService;
use App\Service\ReplaceKeywordsService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SendLink
{
    public function __construct(
        private MailerService $mailerService,
        private GuestRepository $guestRepository,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private MessageService $messageService,
        private ReplaceKeywordsService $replaceKeywords,
        private UrlGeneratorInterface $urlGenerator,
        private ProjectDirService $projectDirService,
    ) {
    }

    public function execute(string $email, BikeRide $bikeRide): array
    {
        $guest = $this->guestRepository->findOneByEmail($email);
        if (!$guest) {
            $guest = new Guest();
            $guest->setEmail($email);
            $this->entityManager->persist($guest);
        }
        $slugger = new AsciiSlugger();
        $token = bin2hex(random_bytes(32));
        $guest
            ->setToken($token)
            ->setTokenExpiresAt(new DateTimeImmutable('+1 hour'));
        $this->entityManager->flush();

        $subject = sprintf('%s - %s', $this->parameterBag->get('club_name'), $bikeRide->getTitle());
        $link = $this->urlGenerator->generate('session_guest_add', [
                'bikeRide' => $bikeRide->getId(),
                'slug' => $slugger->slug($bikeRide->getTitle()),
                'token' => $token
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        $content = $this->replaceKeywords->replaceWhithParams($this->messageService->getMessageByName('GUEST_LINK_AUTHENTIFICATION'), [
            '{{ lien_inscription }}' => sprintf('<a href="%s">%s</a>', $link, $link),
        ]);
        $attachments = $bikeRide->getRules() 
            ? [$this->projectDirService->dir('upload', $bikeRide->getRules())]
            : [];

        return $this->mailerService->sendMailToParticipant($guest, $subject, $content, $attachments);
    }
}
