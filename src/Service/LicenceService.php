<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
use App\Entity\User;
use App\ViewModel\UserViewModel;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;

class LicenceService
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getCurrentSeason(): int
    {
        $today = new DateTime();

        return (8 < (int) $today->format('m')) ? (int) $today->format('Y') + 1 : (int) $today->format('Y');
    }

    public function getCategory(User $user): int
    {
        $today = new DateTime();
        $age = $today->diff($user->getIdentities()->first()->getBirthDate());

        return (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
    }

    public function getRegistrationTitle(UserViewModel $user): string
    {
        $title = $this->translator->trans('registration_step.type.default');
        $licence = $user->seasonLicence;
        $title = 'registration_step.type.';
        if (null !== $licence) {
            if (! $licence->isFinal) {
                $title .= 'testing';
            } else {
                $category = $licence->category;
                if (null !== $category) {
                    $categories = [
                        Licence::CATEGORY_MINOR => 'minor',
                        Licence::CATEGORY_ADULT => 'adult',
                    ];
                    $title .= $categories[$category];
                }
            }
        }

        return $this->translator->trans($title);
    }

    public function getSeasonsStatus(): array
    {
        $today = new DateTime();
        $currentSeason = $this->getCurrentSeason();

        $seasonsStatus = [];

        $seasonsStatus[Licence::STATUS_NONE] = ((int) $today->format('m') < 9) ? $currentSeason - 2 : $currentSeason - 1;

        $seasonsStatus[Licence::STATUS_WAITING_RENEW] = (9 < (int) $today->format('m')) ? $currentSeason - 1 : 1970;

        return $seasonsStatus;
    }

    public function getSeasonByStatus(int $status): int
    {
        return $this->getSeasonsStatus()[$status];
    }
}
