<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideDto;
use App\Dto\BikeRideTypeDto;
use App\Entity\BikeRide;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Session;
use App\Entity\User;
use App\Mapper\DropdownMapper;
use App\Repository\SessionRepository;
use App\Service\BikeRideService;
use App\Service\ProjectDirService;
use App\UseCase\BikeRide\IsRegistrable;
use App\UseCase\BikeRide\IsWritableAvailability;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class BikeRideDtoTransformer
{
    private DateTimeImmutable $today;
        
    public function __construct(
        private Security $security,
        private IsWritableAvailability $isWritableAvailability,
        private IsRegistrable $isRegistrable,
        private ProjectDirService $projectDirService,
        private BikeRideTypeDtoTransformer $bikeRideTypeDtoTransformer,
        private SurveyDtoTransformer $surveyDtoTransformer,
        private SessionRepository $sessionRepository,
        private BikeRideService $bikeRideService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private DropdownMapper $dropdownMapper,
    ) {
    }

    public function fromEntity(?BikeRide $bikeRide, ?array $userAvailableSessions = null): BikeRideDto
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $this->today = (new DateTimeImmutable())->setTime(0, 0, 0);

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

            $dateTimePeriod = $this->bikeRideService->getDateTimePeriod($bikeRide);
            $bikeRideDto->displayClass = $this->getDisplayClass($dateTimePeriod);
            $bikeRideDto->period = $this->bikeRideService->getPeriod($bikeRide);

            $bikeRideDto->survey = ($bikeRide->getSurvey()) ? $this->surveyDtoTransformer->fromEntity($bikeRide->getSurvey()) : null;
            $bikeRideDto->members = $this->getMembers($bikeRideDto->startAt, $bikeRideDto->bikeRideType, $bikeRide->getClusters());

            $bikeRideDto->filename = $this->getFilename($bikeRide->getFileName());
            $bikeRideDto->rules = $this->getFilename($bikeRide->getRules());
            $bikeRideDto->securityGuidelines = $this->getFilename($bikeRide->getSecurityGuidelines());
            $bikeRideDto->rulesThumbnail = $this->getFilename($bikeRide->getRulesThumbnail());
            $bikeRideDto->securityGuidelinesThumbnail = $this->getFilename($bikeRide->getSecurityGuidelinesThumbnail());
            $bikeRideDto->display = $this->display($bikeRide->isPrivate(), $user, $dateTimePeriod);
            $bikeRideDto->isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
            $bikeRideDto->btnRegistration = $this->getBtnRegistration($bikeRide, $user, $userAvailableSessions);
            $bikeRideDto->isPublic = $bikeRide->getBikeRideType()->isPublic();
            $bikeRideDto->tracks = $this->getTracks($bikeRide);
        }

        return $bikeRideDto;
    }

    public function getHeaderFromEntity(?BikeRide $bikeRide, ?array $surveyHistories = null, ?AvailabilityEnum $availability = AvailabilityEnum::REGISTERED): BikeRideDto
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
            $bikeRideDto->survey = (AvailabilityEnum::REGISTERED === $availability && $bikeRide->getSurvey()) ? $this->surveyDtoTransformer->fromEntity($bikeRide->getSurvey(), $surveyHistories) : null;
            $bikeRideDto->period = $this->bikeRideService->getPeriod($bikeRide);
            $bikeRideDto->isEditable = $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide);
            $bikeRideDto->isMultiClusters = 1 < $bikeRide->getClusters()->count();
            if ($bikeRide->getMinAge()) {
                $bikeRideDto->minAge = sprintf('A partir de %s ans', $bikeRide->getMinAge());
            }
            $bikeRideDto->survey = ($bikeRide->getSurvey()) ? $this->surveyDtoTransformer->fromEntity($bikeRide->getSurvey()) : null;
            $bikeRideDto->showFramerAndMemberList = $this->getShowFramerAndMemberList($bikeRide);
            $bikeRideDto->isPublic = $bikeRide->getBikeRideType()->isPublic();
        }

        return $bikeRideDto;
    }

    public function fromEntities(Paginator|Collection|array $bikeRideEntities): array
    {
        $bikeRides = [];
        /** @var User $user */
        $user = $this->security->getUser();
        $userAvailableSessions = ($user instanceof Member) ? $this->sessionRepository->findAvailableByUser($user) : null;
        foreach ($bikeRideEntities as $bikeRideEntity) {
            $bikeRides[] = $this->fromEntity($bikeRideEntity, $userAvailableSessions);
        }

        return $bikeRides;
    }

    public function shedulefromEntities(Paginator|Collection|array $bikeRideEntities): array
    {
        $bikeRides = [];
        /** @var User $user */
        $user = $this->security->getUser();
        foreach ($bikeRideEntities as $bikeRideEntity) {
            $bikeRideDto = new BikeRideDto();
            $bikeRideDto->id = $bikeRideEntity->getId();
            $bikeRideDto->startAt = $bikeRideEntity->getStartAt();
            $bikeRideDto->title = $bikeRideEntity->getTitle();
            $bikeRideDto->bikeRideType = $this->bikeRideTypeDtoTransformer->shedulefromEntity($bikeRideEntity->getBikeRideType());

            $bikeRideDto->dropdown = $this->dropdownMapper->fromBikeRide($bikeRideEntity);

            $bikeRides[] = $bikeRideDto;
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

    public function isOver(array $dateTimePeriod): bool
    {
        return $dateTimePeriod['closingAt'] < $this->today;
    }

    public function isNext(array $dateTimePeriod): bool
    {
        return $dateTimePeriod['displayAt'] <= $this->today && $this->today <= $dateTimePeriod['closingAt'];
    }

    private function getDisplayClass(array $dateTimePeriod): string
    {
        if ($this->isNext($dateTimePeriod)) {
            return ' active';
        }
        if ($this->isOver($dateTimePeriod)) {
            return ' disable';
        }

        return '';
    }

    private function getFilename(?string $filename): ?string
    {
        return ($filename) ? $this->projectDirService->dir('', 'upload', $filename) : null;
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
    private function display(bool $private, ?User $user, array $dateTimePeriod): bool
    {
        if ($this->isOver($dateTimePeriod)) {
            return true;
        }

        return !($private && !$$user);
    }

    private function getUnregistrable(?array $userAvailableSessions, BikeRide $bikeRide): false|array
    {
        if (!$userAvailableSessions) {
            return false;
        }
        /** @var Session $session */
        foreach ($userAvailableSessions as $session) {
            if ($session->getCluster()->getBikeRide() === $bikeRide) {
                return (AvailabilityEnum::NONE !== $session->getAvailability())
                ? [
                    'link' => $this->urlGenerator->generate('session_availability_edit', ['session' => $session->getId()]),
                    'btnLabel' => '<i class="fas fa-edit"></i> Modifier sa disponibilité',
                ]
                : [
                    'link' => $this->urlGenerator->generate('session_delete', ['session' => $session->getId()]),
                    'btnLabel' => '<i class="fas fa-times-circle"></i> Se désinscrire',
                ];
            }
        }

        return false;
    }

    private function getRangeAge(BikeRide $bikeRide): ?string
    {
        if (!$bikeRide->getMinAge()) {
            return null;
        }
        if ($bikeRide->getMaxAge()) {
            return sprintf('De %d jusqu\'à %d ans', $bikeRide->getMinAge(), $bikeRide->getMaxAge());
        }
        return sprintf('A partir de %d ans', $bikeRide->getMinAge());
    }

    private function getBtnRegistration(BikeRide $bikeRide, ?User $user, ?array $userAvailableSessions): ?array
    {
        $unregistrable = $this->getUnregistrable($userAvailableSessions, $bikeRide);
        if ($unregistrable) {
            $unregistrable['modal'] = false;
            return $unregistrable;
        }

        $isWritableAvailability = $this->isWritableAvailability->execute($bikeRide, $user);
        $isRegistrable = $this->isRegistrable->execute($bikeRide, $user);
        if (!$isWritableAvailability && $isRegistrable && $this->bikeRideService->registrationClosed($bikeRide, $user)) {
            return [
                'link' => $this->urlGenerator->generate('registration_closed', ['bikeRide' => $bikeRide->getId()]),
                'modal' => true,
                'btnLabel' => sprintf('<i class="fas fa-chevron-circle-right"></i> %s', 'S\'incrire'),
            ];
        }

        if ($isRegistrable || $isWritableAvailability) {
            $slugger = new AsciiSlugger();
            return [
                'link' => ($bikeRide->getBikeRideType()->isPublic())
                    ? $this->urlGenerator->generate('bike_ride_detail', [
                        'bikeRide' => $bikeRide->getId(),
                        'slug' => $slugger->slug($bikeRide->getTitle())
                    ])
                    : $this->urlGenerator->generate('session_add', ['bikeRide' => $bikeRide->getId()]),
                'btnLabel' => sprintf('<i class="fas fa-chevron-circle-right"></i> %s', ($isWritableAvailability) ? 'Disponibilité' : 'S\'incrire'),
                'modal' => false,
            ];
        }

        return null;
    }

    private function getShowFramerAndMemberList(BikeRide $bikeRide): bool
    {
        /** @var Member $user */
        $user = $this->security->getUser();

        return $bikeRide->getBikeRideType()->isNeedFramers() && $user->getLevel()->getType() === Level::TYPE_FRAME;
    }

    private function getTracks(BikeRide $bikeRide): array
    {
        $tracks = [];
        foreach ($bikeRide->getBikeRideTracks() as $track) {
            $filename = base64_encode($track->getFilename());
            $tracks[] = [
                'label' => $track->getLabel(),
                'thumbnail' => $this->urlGenerator->generate('bike_ride_track', ['filename' => base64_encode($track->getThumbnail()), 'mimeType' => 'image']),
                ($track->getThumbnail()) ? $this->projectDirService->dir('', 'bike_ride_track', $track->getThumbnail()) : null,
                'links' => [
                    'gpx' => $this->urlGenerator->generate('bike_ride_track', ['filename' => $filename, 'mimeType' => 'gpx']),
                    'zip' => $this->urlGenerator->generate('bike_ride_track', ['filename' => $filename, 'mimeType' => 'zip'])
                ]
            ];
        }

        return $tracks;
    }
}
