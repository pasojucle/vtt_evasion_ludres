<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardItemView;
use App\Dto\View\Dashboard\DashboardListView;
use App\Entity\SecondHand;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardSecondHandMapper
{
    private const string TO_VALIDATE = 'to_validate';

    private const string VALID = 'valid';

    private const array TYPES = [
        self::TO_VALIDATE => 'dashboard.second_hand.to_validate',
        self::VALID => 'dashboard.second_hand.valid',
    ];

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param SecondHand[] $secondHands
     */
    public function mapToView(array $secondHands): DashboardListView
    {
        $items = [];
        foreach ($this->countSecondHandByType($secondHands) as $type => $count) {
            $items[] = new DashboardItemView(
                label: $this->translator->trans(self::TYPES[$type]),
                badge: new BadgeView(
                    (string) $count
                )
            );
        }

        return new DashboardListView(
            id: 'dashboard-second-hands',
            items: $items
        );
    }

    /**
     * @param SecondHand[] $secondHands
     * @return array<string, int>
     */
    private function countSecondHandByType(array $secondHands): array
    {
        $counters = [self::TO_VALIDATE => 0, self::VALID => 0];

        /** @var SecondHand $secondHand */
        foreach ($secondHands as $secondHand) {
            $type = (null !== $secondHand->getValidedAt()) ? self::TO_VALIDATE : self::VALID;
            ++$counters[$type];
        }

        return $counters;
    }
}
