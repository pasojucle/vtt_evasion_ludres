<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideDto;
use App\Dto\BikeRideTypeDto;
use App\Entity\BikeRide;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Service\ProjectDirService;
use App\Twig\AppExtension;
use App\UseCase\BikeRide\IsRegistrable;
use App\UseCase\BikeRide\IsWritableAvailability;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;

class BikeRideDtoTransformer
{
    private DateTimeImmutable $today;
    
    private DateTimeImmutable $displayAt;
    
    private DateTimeImmutable $closingAt;
    

    public function __construct(
        private Security $security,
        private AppExtension $appExtension,
        private IsWritableAvailability $isWritableAvailability,
        private IsRegistrable $isRegistrable,
        private ProjectDirService $projectDirService,
        private BikeRideTypeDtoTransformer $bikeRideTypeDtoTransformer,
        private SurveyDtoTransformer $surveyDtoTransformer,
        private SessionRepository $sessionRepository,
    ) {
        $this->today = (new DateTimeImmutable())->setTime(0, 0, 0);
    }

    public function fromEntity(?BikeRide $bikeRide, ?array $userAvailableSessions = null): BikeRideDto
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $bikeRideDto = new BikeRideDto();
        if ($bikeRide) {
            $bikeRideDto->id = $bikeRide->getId();
            $bikeRideDto->bikeRideType = $this->bikeRideTypeDtoTransformer->fromEntity($bikeRide->getBikeRideType());
            $bikeRideDto->title = $bikeRide->getTitle();
            $bikeRideDto->type = $bikeRide->getBikeRideType()->getName();
            $bikeRideDto->content = $bikeRide->getContent();
            $bikeRideDto->startAt = $bikeRide->getStartAt();
            $bikeRideDto->endAt = $bikeRide->getEndAt();
            $bikeRideDto->closingDuration = $bikeRide->getClosingDuration();
            $bikeRideDto->displayDuration = $bikeRide->getDisplayDuration();
            $bikeRideDto->rangeAge = $this->getRangeAge($bikeRide);

            $this->displayAt = $bikeRideDto->startAt->setTime(0, 0, 0);
            $this->closingAt = $bikeRideDto->startAt->setTime(23, 59, 59);
            $bikeRideDto->displayClass = $this->getDisplayClass($bikeRideDto->displayDuration);
            $bikeRideDto->period = $this->getPeriod($bikeRideDto->startAt, $bikeRideDto->endAt);
            $bikeRideDto->isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $user);
            $bikeRideDto->isRegistrable = $this->isRegistrable->execute($bikeRide, $user);
            $bikeRideDto->unregistrable = $this->getUnregistrable($userAvailableSessions, $bikeRide);
            $bikeRideDto->btnLabel = $this->getBtnLabel($bikeRideDto);
            $bikeRideDto->survey = ($bikeRide->getSurvey()) ? $this->surveyDtoTransformer->fromEntity($bikeRide->getSurvey()) : null;
            $bikeRideDto->members = $this->getMembers($bikeRideDto->startAt, $bikeRideDto->bikeRideType, $bikeRide->getClusters());

            $bikeRideDto->filename = $this->getFilename($bikeRide->getFileName());
            $bikeRideDto->display = $this->display($bikeRide->isPrivate(), $user);
            $bikeRideDto->isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
        }

        return $bikeRideDto;
    }

    public function getHeaderFromEntity(?BikeRide $bikeRide): BikeRideDto
    {
        $bikeRideDto = new BikeRideDto();
        if ($bikeRide) {
            $bikeRideDto->id = $bikeRide->getId();
            $bikeRideDto->title = $bikeRide->getTitle();
            $bikeRideDto->shortTitle = $this->getShorTilte($bikeRide->getTitle());
            $bikeRideDto->startAt = $bikeRide->getStartAt();
            $bikeRideDto->endAt = $bikeRide->getEndAt();
            $bikeRideDto->bikeRideType = $this->bikeRideTypeDtoTransformer->fromEntity($bikeRide->getBikeRideType());
            $bikeRideDto->content = $bikeRide->getContent();
            $bikeRideDto->survey = ($bikeRide->getSurvey()) ? $this->surveyDtoTransformer->fromEntity($bikeRide->getSurvey()) : null;
            $bikeRideDto->period = $this->getPeriod($bikeRideDto->startAt, $bikeRideDto->endAt);
            $bikeRideDto->isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
        }

        return $bikeRideDto;
    }

    public function fromEntities(Paginator|Collection|array $bikeRideEntities): array
    {
        $bikeRides = [];
        /** @var User $user */
        $user = $this->security->getUser();
        $userAvailableSessions = ($user) ? $this->sessionRepository->findAvailableByUser($user) : null;
        foreach ($bikeRideEntities as $bikeRideEntity) {
            $bikeRides[] = $this->fromEntity($bikeRideEntity, $userAvailableSessions);
        }

        return $bikeRides;
    }

    private function getShorTilte(string $title): string
    {
        if (1 === preg_match('#^(.+)\s\((.+)\)$#', $title, $matches)) {
            return $matches[1];
        }
        return $title;
    }

    public function isOver(): bool
    {
        return $this->closingAt < $this->today;
    }

    public function isNext(int $displayDuration): bool
    {
        $interval = new DateInterval('P' . $displayDuration . 'D');

        return $this->displayAt->sub($interval) <= $this->today && $this->today <= $this->closingAt;
    }

    private function getDisplayClass(int $displayDuration): string
    {
        if ($this->isNext($displayDuration)) {
            return ' active';
        }
        if ($this->isOver()) {
            return ' disable';
        }

        return '';
    }

    private function getBtnLabel(?BikeRideDto $bikeRide): string
    {
        if ($bikeRide->isWritableAvailability) {
            return 'Disponibilité';
        }

        return 'S\'incrire';
    }

    private function getPeriod(DateTimeImmutable $startAt, ?DateTimeImmutable $endAt): string
    {
        return  (null === $endAt)
            ? $this->appExtension->formatDateLong($startAt)
            : $this->appExtension->formatDateLong($startAt) . ' au ' . $this->appExtension->formatDateLong($endAt);
    }

    private function getFilename(?string $filename): ?string
    {
        return ($filename) ? $this->projectDirService->dir('','upload', $filename) : null;
    }

    private function getMembers(DateTimeImmutable $startAt, BikeRideTypeDto $bikeRideType, Collection $clusters): string
    {
        $members = 0;
        if ($startAt < $this->today && $bikeRideType->isRegistrable) {
            foreach ($clusters as $cluster) {
                foreach ($cluster->getSessions() as $session) {
                    if ($session->isPresent()) {
                        ++$members;
                    }
                }
            }
        }

        if (0 < $members) {
            return  sprintf('%s participants', $members);
        }

        return '';
    }
    private function display(bool $private, ?User $user): bool
    {
        if ($this->isOver()) {
            return true;
        }

        return !($private && !$user);
    }

    private function getUnregistrable(?array $userAvailableSessions, BikeRide $bikeRide): false|array
    {
        if (!$userAvailableSessions) {
            return false;
        }
        /** @var Session $session */
        foreach ($userAvailableSessions as $session) {
            if ($session->getCluster()->getBikeRide() === $bikeRide) {
                return ($session->getAvailability())
                ? [
                    'route' => 'session_availability_edit',
                    'sessionId' => $session->getId(),
                    'btnLabel' => '<i class="fas fa-edit"></i> Modifier sa disponibilité',
                ]
                : [
                    'route' => 'session_delete',
                    'sessionId' => $session->getId(),
                    'btnLabel' => '<i class="fas fa-times-circle"></i> Se désinscrire',
                ];
            }
        }

        return false;
    }

    private function getRangeAge(BikeRide $bikeRide): ?string
    {
        if ($bikeRide->getMinAge() && $bikeRide->getMaxAge()) {
            return sprintf('De %d jusqu\'à %d ans', $bikeRide->getMinAge(), $bikeRide->getMaxAge());
        }
        return null;
    }
}
