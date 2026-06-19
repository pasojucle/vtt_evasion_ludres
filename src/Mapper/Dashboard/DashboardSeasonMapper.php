<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardItemView;
use App\Dto\View\Dashboard\DashboardListView;
use App\Entity\Licence;
use App\Service\ParameterService;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardSeasonMapper
{
    private const string MEMBER = 'member';
    private const string TESTING = 'testing';
    private const string REGISTRATION = 'registration';
    private const string RE_REGISTRATION = 're_registration';

    private const array TYPES = [
        self::MEMBER => 'dashboard.season.member',
        self::TESTING => 'dashboard.season.testing',
        self::REGISTRATION => 'dashboard.season.registration',
        self::RE_REGISTRATION => 'dashboard.season.re_registration',
    ];

    public function __construct(
        private TranslatorInterface $translator,
        private ParameterService $parameterService,
    ) {
    }

    /**
     * @param array<int, array{licence: Licence, hasPreviousLicence: string|int}> $results
     */
    public function mapToView(array $results): DashboardListView
    {
        $items = [];
        foreach ($this->countLicencesByType($results) as $type => $count) {
            $items[] = new DashboardItemView(
                label: $this->translator->trans(self::TYPES[$type]),
                badge: new BadgeView(
                    (string) $count,
                )
            );
        }

        return new DashboardListView(
            id: 'dashboard-season',
            items: $items,
            parameters: [
                new DashboardItemView(
                    label: 'Inscription séances d\'essai (Ecole Vtt)',
                    badge: $this->getBadgeStateParameter('SCHOOL_TESTING_REGISTRATION'),
                ),
                new DashboardItemView(
                    label: 'Ré-inscription',
                    badge: $this->getBadgeStateParameter('NEW_SEASON_RE_REGISTRATION_ENABLED'),
                ),
            ],
        );
    }

    /**
     * @param array<int, array{licence: Licence, hasPreviousLicence: string|int}> $results
     */
    private function countLicencesByType(array $results): array
    {
        $counters = [self::MEMBER => 0, self::TESTING => 0, self::REGISTRATION => 0, self::RE_REGISTRATION => 0];

        foreach ($results as $row) {
            /** @var Licence $licence */
            $licence = $row['licence'];
            $hasPreviousLicence = (bool) $row['hasPreviousLicence'];

            if (!$licence->getState()->isYearly()) {
                $counters[self::TESTING]++;
                continue;
            }

            if ($licence->getState()->isValid()) {
                $counters[self::MEMBER]++;
                continue;
            }

            $type = $hasPreviousLicence ? self::RE_REGISTRATION : self::REGISTRATION;
            $counters[$type]++;
        }

        return $counters;
    }

    private function getBadgeStateParameter(string $name): BadgeView
    {
        $value = $this->parameterService->getParameterByName($name);
        if (true === $value) {
            return new BadgeView(
                value: 'lucide:toggle-right',
                variant: ColorVariant::SUCCESS,
                size: Size::ICON
            );
        }

        return new BadgeView(
            value: 'lucide:toggle-left',
            variant: ColorVariant::DESTRUCTIVE,
            size: Size::ICON
        );
    }
}
