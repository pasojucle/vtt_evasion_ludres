<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ClusterDto;
use App\Entity\Address;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\GardianKindEnum;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Guest;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\MemberGardian;
use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\CacheService;
use App\Service\ClusterService;
use App\Service\IdentityService;
use App\Service\LevelService;
use App\Service\LicenceAgreementService;
use App\Service\SessionService;
use App\Service\UserService;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ClusterDtoTransformer
{
    public function __construct(
        private LevelDtoTransformer $levelDtoTransformer,
        private DropdownDtoTransformer $dropdownDtoTransformer,
        private CacheService $cacheService,
        private ClusterService $clusterService,
        private SessionRepository $sessionRepository,
        private readonly Security $security,
        private readonly LevelService $levelService,
        private readonly LicenceAgreementService $licenceAgreementService,
        private readonly SessionService $sessionService,
        private UserService $userService,
        private IdentityService $identityService,
        private TranslatorInterface $translator,
    ) {
    }

    public function detailFromEntity(Cluster $cluster): ClusterDto
    {
        $clusterDto = new ClusterDto();

        $clusterDto->id = $cluster->getId();
        $clusterDto->title = $cluster->getTitle();
        $clusterDto->isComplete = $cluster->isComplete();
        $sessionEntities = $cluster->getSessions();
        $clusterDto->sessions = $this->getAvailableSessions($sessionEntities, $cluster->getBikeRide()->getBikeRideType());
        $clusterDto->hasSkills = !$cluster->getSkills()->isEmpty();
        $clusterDto->usersOnSiteCount = $this->getUsersOnSiteCount($sessionEntities, $cluster->getBikeRide());
        $clusterDto->isEditable = $this->security->isGranted('CLUSTER_EDIT', $cluster);

        return $clusterDto;
    }

    public function exportFromEntity(Cluster $cluster): ClusterDto
    {
        $clusterDto = new ClusterDto();

        $clusterDto->id = $cluster->getId();
        $clusterDto->title = $cluster->getTitle();
        $clusterDto->isComplete = $cluster->isComplete();
        $sessionEntities = $cluster->getSessions();
        $clusterDto->sessions = $this->getSessions($sessionEntities, false);
        $clusterDto->hasSkills = !$cluster->getSkills()->isEmpty();
        $clusterDto->usersOnSiteCount = $this->getUsersOnSiteCount($sessionEntities, $cluster->getBikeRide());
        $clusterDto->isEditable = $this->security->isGranted('CLUSTER_EDIT', $cluster);

        return $clusterDto;
    }


    public function fromEntity(Cluster $cluster, $sessionEntities = null): ClusterDto
    {
        $cachePool = $this->cacheService->getCache();
        $clusterCache = $cachePool->getItem($this->cacheService->getCacheIndex($cluster));
        // if (!$clusterCache->isHit()) {
        $fromEntities = true;
        if (!$sessionEntities) {
            $sessionEntities = $cluster->getSessions();
            $fromEntities = false;
        }

        $clusterDto = new ClusterDto();
        $clusterDto->id = $cluster->getId();
        $clusterDto->title = $cluster->getTitle();
        $clusterDto->level = $this->levelDtoTransformer->fromEntity($cluster->getLevel());
        $clusterDto->sessions = $this->getSessions($sessionEntities, $fromEntities);
        $clusterDto->maxUsers = $cluster->getMaxUsers();
        $clusterDto->role = $cluster->getRole();
        $clusterDto->isComplete = $cluster->isComplete();
        $clusterDto->memberSessions = $this->clusterService->getMemberSessions($cluster);
        $clusterDto->availableSessions = $this->getAvailableSessions($sessionEntities, $cluster->getBikeRide()->getBikeRideType());
        $clusterDto->usersOnSiteCount = $this->getUsersOnSiteCount($sessionEntities, $cluster->getBikeRide());
        $clusterDto->hasSkills = !$cluster->getSkills()->isEmpty();
        $clusterCache->set($clusterDto);
        $clusterCache->expiresAfter(DateInterval::createFromDateString('1 hour'));
        $cachePool->save($clusterCache);
        // }

        $clusterDto = $clusterCache->get();
        $clusterDto->isEditable = $this->security->isGranted('CLUSTER_EDIT', $cluster);

        return $clusterDto;
    }
    
    public function fromBikeRide(BikeRide $bikeRide): array
    {
        $sessionsByClusters = [];
        /** @var Session $session */
        foreach ($this->sessionRepository->findByBikeRideId($bikeRide->getId()) as $session) {
            $clusterId = $session->getCluster()->getId();
            $sessionsByClusters[$clusterId][] = $session;
        }

        $clusters = [];
        foreach ($bikeRide->getClusters() as $clusterEntity) {
            $sessions = (array_key_exists($clusterEntity->getId(), $sessionsByClusters)) ? $sessionsByClusters[$clusterEntity->getId()] : [];
            $clusters[] = $this->fromEntity($clusterEntity, new ArrayCollection($sessions));
        }

        return $clusters;
    }
    
    public function headerFromBikeRide(BikeRide $bikeRide): array
    {
        $clusters = [];
        /** @var Cluster $clusterEntity */
        foreach ($bikeRide->getClusters() as $clusterEntity) {
            $clusterDto = new ClusterDto();
            $clusterDto->id = $clusterEntity->getId();
            $clusterDto->title = $clusterEntity->getTitle();
            $clusters[] = $clusterDto;
        }

        return $clusters;
    }

    private function getSessions(Collection $sessionEntities, bool $fromEntities): array
    {
        $sessions = [];
        /** @var Session $session */
        foreach ($sessionEntities as $session) {
            $userEntity = $session->getUser();
            $identity = $userEntity->getIdentity();
            $bithDate = $identity->getBirthDate();
            list($birthPlace, $birthDepartment, $birthCountry) = $this->identityService->getBirthplace($identity);
            $userAddress = $this->addressToArray($identity->getAddress());
            $userLicence = $userEntity->getLastLicence();
            $user = [
                'id' => $userEntity->getId(),
                'fullName' => $identity->getName() . ' ' . $identity->getFirstName(),
                'birthDate' => ($bithDate) ? $bithDate->format('j/n/Y') : null,
                'address' => $userAddress,
                'birthPlace' => $birthPlace,
                'birthDepartment' => $birthDepartment,
                'birthCountry' => $birthCountry,
                'email' => ($userEntity instanceof Guest) ? $userEntity->getEmail() : $identity->getEmail(),
                'phone' => implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()])),
                'emergencyPhone' => $identity->getEmergencyPhone(),
                'emergencyContact' => $identity->getEmergencyContact(),
                'picture' => $this->identityService->getPicture($identity->getPicture()),
            ];
            if ($userEntity instanceof Member) {
                $level = $userEntity->getLevel();
                $user['level'] = [
                    'title' => $level->getTitle(),
                    'colors' => $this->levelService->getColors($level->getColor()),
                ];
                $licencesAgreements = [];
                foreach ($userEntity->getLastLicence()->getLicenceAgreements() as $licenceAgreement) {
                    $agreement = $licenceAgreement->getAgreement();
                    $licencesAgreements[$agreement->getId()] = $this->licenceAgreementService->toHtml($licenceAgreement);
                }
                $user['agreements'] = $licencesAgreements;
                foreach ([$userEntity->getLegalGardian(), $userEntity->getSecondContact()] as $gardian) {
                    $this->addGardian($gardian, $user, $userAddress);
                }
                $user['health'] = [
                    'content' => $userEntity->getHealth()?->getContent()
                ];
                $user['lastLicence'] = [
                    'coverageStr' => (!empty($userLicence->getCoverage()))
                        ? $this->translator->trans(Licence::COVERAGES[$userLicence->getCoverage()])
                        : null
                ];
            }

            $sessions[] = [
                // 'user' => ($fromEntities)
                //     ? $this->userDtoTransformer->getSessionHeaderFromEntity($session->getUser())
                //     : $this->userDtoTransformer->fromEntity($session->getUser()),
                'id' => $session->getId(),
                'user' => $user,
                'availability' => $session->getAvailability(),
                'isPresent' => $session->isPresent(),
                'bikeType' => $session->getBikeType(),
            ];
        }

        return $sessions;
    }

    private function addGardian(?MemberGardian $gardian, array &$user, array $userAddress): void
    {
        if ($gardian) {
            $key = GardianKindEnum::LEGAL_GARDIAN === $gardian->getKind() ? 'legalGardian' : 'secondKinship';
            $gardianIdentity = $gardian->getIdentity();
            $gardianAddress = $gardianIdentity->getAddress();
            $user[$key] = [
                'kind' => $gardian->getKind(),
                'fullName' => $gardianIdentity->getFullName(),
                'phone' => implode(' - ', array_filter([$gardianIdentity->getMobile(), $gardianIdentity->getPhone()])),
                'address' => $gardianAddress ? $this->addressToArray($gardianAddress) : $userAddress,
                'email' => $gardianIdentity->getEmail(),
            ];
        }
    }

    private function addressToArray(Address $address): array
    {
        return [
            'street' => $address->getStreet(),
            'postalCode' => $address->getPostalCode(),
            'town' => $address->getCommune()?->getName() ?? $address->getTown(),
        ];
    }

    private function getAvailableSessions(Collection $sessionEntities, BikeRideType $bikeRideType): array
    {
        $sortedSessions = [];
        $allowedAvailabilities = ($bikeRideType->isRequireAvailability())
            ? [AvailabilityEnum::UNAVAILABLE, AvailabilityEnum::REGISTERED]
            : [AvailabilityEnum::NONE, AvailabilityEnum::AVAILABLE, AvailabilityEnum::REGISTERED];

        /** @var Session $session */
        foreach ($sessionEntities as $session) {
            if (in_array($session->getAvailability(), $allowedAvailabilities)) {
                $user = $session->getUser();
                $identity = $user->getIdentity();
                $level = $user->getLevel();
                $lastLicence = $user->getLastLicence();
                $licencesAgreements = [];
                $health = null;
                $isEndTesting = false;
                $mustProvideRegistration = false;

                if ($user instanceof Member) {
                    foreach ($user->getLastLicence()->getLicenceAuthorizations() as $licenceAgreement) {
                        $agreement = $licenceAgreement->getAgreement();
                        $licencesAgreements[$agreement->getId()]['toHtml'] = $this->licenceAgreementService->toHtml($licenceAgreement);
                    }
                    $health = $user->getHealth()->getContent();
                    $isEndTesting = $this->userService->isEndTesting($lastLicence, $this->userService->trialSessionsPresent($lastLicence, $user));
                    $mustProvideRegistration = $this->userService->mustProvideRegistration($lastLicence, $user->getLicences()->count());
                }
                $sortedSessions[] = [
                    'id' => $session->getId(),
                    'availability' => $this->sessionService->getAvailability($session->getAvailability()),
                    'user' => [
                        'id' => $user->getId(),
                        'member' => [
                            'fullName' => $identity->getName() . ' ' . $identity->getFirstName(),
                        ],
                        'level' => [
                            'colors' => $this->levelService->getColors($level?->getColor()),
                            'title' => $level?->getTitle(),
                            'type' => $level?->getType(),
                            'accompanyingCertificat' => $level?->isAccompanyingCertificat(),
                        ],
                        'lastLicence' => [
                            'authorizations' => $licencesAgreements
                        ],
                        'health' => [
                            'content' => $health,
                        ],
                        'isEndTesting' => $isEndTesting,
                        'mustProvideRegistration' => $mustProvideRegistration,
                        'licenceNumber' => $user->getLicenceNumber(),
                        'dropdown' => $this->dropdownDtoTransformer->fromSession($session),
                    ],
                    'userIsOnSite' => $session->isPresent(),
                    'practice' => $bikeRideType->isDisplayBikeKind() ? $session->getPractice()->toBadge($this->translator) : null,
                    'bikeType' => $session->getBikeType()->toBadge($this->translator),
                ];
            }
        }
        usort($sortedSessions, function ($a, $b) {
            $a = strtolower($a['user']['member']['fullName']);
            $b = strtolower($b['user']['member']['fullName']);

            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return $sortedSessions;
    }

    private function getUsersOnSiteCount(Collection $sessionEntities, BikeRide $bikeRide): int
    {
        $userOnSiteSessions = [];
        foreach ($sessionEntities as $session) {
            if (RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
                $level = $session->getMember()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : Level::TYPE_SCHOOL_MEMBER;
                if ($session->isPresent() && Level::TYPE_SCHOOL_MEMBER === $levelType) {
                    $userOnSiteSessions[] = $session;
                }
            } else {
                if ($session->isPresent()) {
                    $userOnSiteSessions[] = $session;
                }
            }
        }
        
        return count($userOnSiteSessions);
    }
}
