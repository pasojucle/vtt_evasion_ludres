<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;


use App\Core\Contract\Provider\StreamListExportableInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\ParticipationFilter;
use App\Entity\Enum\LevelType;
use App\Mapper\LevelFilterMapper;
use App\Mapper\Participation\ParticipationListExportMapper;
use App\Repository\LevelRepository;
use App\Repository\SessionRepository;
use App\State\Participation\Trait\ParticipationDataProviderTrait;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements StreamListExportableInterface<ParticipationFilter>
 */
class ParticipationListExportProvider implements StreamListExportableInterface
{
    use FilterHydratorTrait;
    use ParticipationDataProviderTrait;

    public function __construct(
        private SessionRepository $sessionRepository,
        private LevelRepository $levelRepository,
        private ParticipationListExportMapper $mapper,
        private LevelFilterMapper $levelFilterMapper,
        private TranslatorInterface $translator,
    ) {
    }

    public function streamExportContent(AbstractFilter $filter, ?HandlerContext $context = null): void
    {
        $sessions = $this->getQueryBuilder($filter)->getQuery()->getResult();
        [$participants, $participationsByActivity] = $this->getParticipationsByActivity($sessions);
  
        $this->mapper->streamToCsv(
            $participants,
            $participationsByActivity,
            $filter,
            $this->hydrateLevels($filter),
        );
    }

    private function getQueryBuilder(ParticipationFilter $filter): QueryBuilder
    {
        $qb = $this->sessionRepository->getSessionQuery();

        $this->sessionRepository->filterParticipated($qb);

        if ($filter->startAt && $filter->endAt) {
            $this->sessionRepository->filterPeriod($qb, $filter->startAt, $filter->endAt);
        };

        if (!empty($filter->levels)) {
            [$levelTypes, $levels] = $this->levelFilterMapper->parseRawLevels($filter->levels);
            $this->sessionRepository->filterLevels($qb, $levelTypes, $levels);
        }

        if ($filter->practice) {
            $this->sessionRepository->filterPractice($qb, $filter->practice);
        }

        return $qb;
    }

    private function getParticipationsByActivity(array $sessions): array
    {
        $defaultParticipations = [];
        $participationsByActivity = [];
        $participants = [];
        foreach($sessions as $session) {
            $user = $session->getUser();
            $userId = $user->getId();
            if (!array_key_exists($userId, $participants)) {
                $identity = $user->getIdentity();
                $participants[$userId] = [
                    'id' => $userId,
                    'entity' => $user,
                    'session' => null,
                    'fullName' => $identity->getFullName(),
                ];
            }
        }
        usort($participants, fn($a, $b) => strcmp($a['fullName'],$b['fullName']));
        $defaultParticipations = array_column($participants, 'session', 'id');

        foreach($sessions as $session) {
            $user = $session->getUser();
            $activity = $session->getCluster()->getBikeRide();
            $activityId = $activity->getId();
            if (!array_key_exists($activityId, $participationsByActivity)) {
                $participationsByActivity[$activityId] = [
                    'activity' => $activity, 
                    'sessions' => $defaultParticipations,
                ];
            }
            $participationsByActivity[$activityId]['sessions'][$user->getId()] = $session;
        }
        usort($participationsByActivity, fn($a, $b) => $a['activity']->getStartAt() < $b['activity']->getStartAt() ? -1 : 1);

        return [
            array_column($participants, 'entity', 'id'),
            $participationsByActivity,
        ];
    }

    private function hydrateLevels(ParticipationFilter $filter): array
    {
        $levels = [];
        foreach($filter->levels as $value) {
            if (is_numeric($value)) {
                $entity = $this->levelRepository->findOneById((int) $value);
                if (null !== $entity) {
                    $levels[] = $entity->getTitle();
                    continue; 
                } 
            }
            $enum = LevelType::tryFrom($value);
            if ($enum instanceof LevelType) {
                $levels[] = $enum->trans($this->translator);
            }
        }

        return $levels;
    }

    public function filename(?HandlerContext $context = null): string
    {
        return 'export_participations.csv';
    }
}
