<?php

declare(strict_types=1);

namespace App\Mapper\MemberParticipation;

use App\Dto\Filter\MemberParticipationFilter;
use App\Entity\Session;
use App\Mapper\Activity\ActivityPeriodMapper;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberParticipationExportMapper
{
    private const CSV_SEPARATOR = ",";
    
    public function __construct(
        private ActivityPeriodMapper $activityPeriodMapper,
        private TranslatorInterface $translator,
    ) {
    }

    public function streamToCsv(array $entities, MemberParticipationFilter $filter): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fp = fopen('php://output', 'w');
        
        $this->addExportHeader($filter, $fp);
        fputcsv($fp, [], self::CSV_SEPARATOR);
        $this->addExportContent($entities, $fp);

        fclose($fp);
    }

    private function addExportHeader(MemberParticipationFilter $filter, $fp): void
    {
        $member = $filter->member;
        $identity = $member->getIdentity();
        $row = [$identity->getFullName(), $member->getLicenceNumber()];
        fputcsv($fp, $row, self::CSV_SEPARATOR);

        if ($filter->startAt && $filter->endAt) {
            $row = [sprintf('Du %s au %s', $filter->startAt->format('d/m/Y'), $filter->endAt->format('d/m/Y'))];
            fputcsv($fp, $row, self::CSV_SEPARATOR);
        }
        if ($filter->type) {
            $row = [sprintf('Type de sortie : %s', $filter->type->getName())];
            fputcsv($fp, $row, self::CSV_SEPARATOR);
        }
    }

    /**
     * @param Session[] $sessions
     */
    private function addExportContent(array $sessions, $fp): void
    {
        $row = ['Date', 'Sortie', 'Présence'];
        fputcsv($fp, $row, self::CSV_SEPARATOR);

        foreach ($sessions as $session) {
            $bikeRide = $session->getCluster()->getBikeRide();
            $row = [
                $this->activityPeriodMapper->mapToView($bikeRide),
                $bikeRide->getTitle(),
                ($session->isPresent())
                    ? $session->getPractice()->trans($this->translator)
                    : '-'
            ];
            fputcsv($fp, $row, self::CSV_SEPARATOR);
            flush();
        }
    }
}
