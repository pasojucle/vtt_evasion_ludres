<?php

declare(strict_types=1);

namespace App\Mapper\Participation;

use App\Dto\Filter\ParticipationFilter;
use App\Entity\BikeRide;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Session;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipationListExportMapper
{
    private const CSV_SEPARATOR = ",";
    
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<int, User> $participants
     * @param array<int, array{activity: BikeRide, sessions: array<int, Session|null>}> $participationsByActivity
     * @param array<string> $levels
     * */
    public function streamToCsv(
        array $participants,
        array $participationsByActivity,
        ParticipationFilter $filter,
        array $levels
    ): void {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fp = fopen('php://output', 'w');

        $this->addExportHeader($filter, $levels, $fp);
        $this->addExportParticipants($participants, $fp);
        $this->addExportActivities($participationsByActivity, $fp);

        fclose($fp);
    }

    private function addExportHeader(ParticipationFilter $filter, array $levels, $fp): void
    {
        if ($filter->startAt && $filter->endAt) {
            fputcsv(
                $fp,
                [sprintf('Du %s au %s', $filter->startAt->format('d/m/Y'), $filter->endAt->format('d/m/Y'))],
                self::CSV_SEPARATOR
            );
        }

        if ($filter->type) {
            fputcsv(
                $fp,
                [sprintf('Type de sortie : %s', $filter->type->getName())],
                self::CSV_SEPARATOR
            );
        }

        if (!empty($levels)) {
            -fputcsv(
                $fp,
                [sprintf('Niveau(x) : %s', implode(' - ', $levels))],
                self::CSV_SEPARATOR
            );
        }

        if ($filter->practice) {
            fputcsv(
                $fp,
                [sprintf('Type de pratique : %s', $filter->practice->trans($this->translator))],
                self::CSV_SEPARATOR
            );
        }
        
        fputcsv($fp, [], self::CSV_SEPARATOR);
        flush();
    }

    private function addExportParticipants(array $participants, $fp): void
    {
        $licenceNumbers = ['Licence'];
        $fullNames = ['Nom'];

        foreach ($participants as $participant) {
            $identity = $participant->getIdentity();
            $licenceNumbers[] = $participant->getLicenceNumber();
            $fullNames[] = $identity->getFullName();
        }
        fputcsv($fp, $licenceNumbers, self::CSV_SEPARATOR);
        fputcsv($fp, $fullNames, self::CSV_SEPARATOR);

        fputcsv($fp, [], self::CSV_SEPARATOR);

        flush();
    }

    private function addExportActivities(array $participationsByActivity, $fp): void
    {
        foreach ($participationsByActivity as $data) {
            $activity = $data['activity'];
            $activityRow = [sprintf('%s - %s', $activity->getStartAt()->format('d/m/Y'), $activity->getTitle())];

            foreach ($data['sessions'] as $session) {
                $practice = $session?->getPractice();
                $activityRow[] = ($practice)
                    ? $practice !== PracticeEnum::NONE ? $practice->trans($this->translator) : 'Présent'
                    : '-';
            }
            fputcsv($fp, $activityRow, self::CSV_SEPARATOR);
            flush();
        }
    }
}
