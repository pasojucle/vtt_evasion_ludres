<?php

declare(strict_types=1);

namespace App\UseCase\Session;

use App\Entity\BikeRide;
use App\Entity\Guest;
use App\Entity\Licence;
use App\Entity\Session;
use App\Repository\PublicRegistrationRateRepository;
use App\Repository\SessionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class AddGuestSession
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionRepository $sessionRepository,
        private PublicRegistrationRateRepository $publicRegistrationRateRepository,
    ) {
    }

    public function getExistingSession(Guest $guest, BikeRide $bikeRide): ?Session
    {
        return $this->sessionRepository->findOneByUserAndBikeRide($guest, $bikeRide);
    }

    public function getNewSession(Guest $guest): Session
    {
        if (null === $guest->getLastLicence()) {
            $licence = new Licence();
            $licence->setSeason(2026);
            $guest->addLicence($licence);
            $this->entityManager->persist($licence);
            $this->entityManager->flush();
        }
        $session = new Session();
        $session->setUser($guest);

        return $session;
    }

    public function setSession(FormInterface $form): Session
    {
        $session = $form->getData();
        $user = $session->getUser();
        $session->setPractice($session->getCluster()->getPractice());
        $licence = $user->getLastLicence();
        if (!$licence->isFFVelo()) {
            $this->entityManager->remove($licence);
        }
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return $session;
    }

    public function getPublicRegistrationRate(): array
    {
        $publicRegistrationRate = [];
        foreach ($this->publicRegistrationRateRepository->findAllOrdered() as $line) {
            $key = sprintf('%s-%s', $line->getPractice()->value, $line->isFFVelo());
            $publicRegistrationRate[$key][] = sprintf('%s : %s', $line->getLabel(), 0 < $line->getAmount()
                ? sprintf('%s €', $line->getAmount() / 100)
                : 'gratuit');
        }
        
        return $publicRegistrationRate;
    }

    public function getAmount(Session $session): false|int
    {
        $participant = $session->getUser();
        $licence = $participant->getLastLicence();
        $birthDate = $participant->getIdentity()->getBirthDate();
        if ($birthDate instanceof DateTime) {
            $age = $birthDate->diff(new DateTime())->y;
            $practice = $session->getCluster()->getPractice();
            $isFFVelo = $licence?->isFFVelo() ?? false;
            $rate = $this->publicRegistrationRateRepository->findOneByPracticeAndAgeAndFFVelo($practice, $age, $isFFVelo);
            if ($rate) {
                return $rate->getAmount();
            }
        }

        return false;
    }
}
