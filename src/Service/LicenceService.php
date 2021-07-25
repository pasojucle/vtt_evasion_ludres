<?php

namespace App\Service;

use DateTime;
use App\Entity\User;
use App\Entity\Licence;
use Symfony\Contracts\Translation\TranslatorInterface;

class LicenceService
{
    private TranslatorInterface $translator;
    public function __construct(TranslatorInterface $translator)
    {
       $this->translator = $translator; 
    }

    public function getCurrentSeason():int
    {
        $today = new DateTime();
        return (8 < (int) $today->format('m')) ? (int) $today->format('Y') + 1 :  (int) $today->format('Y');
    }

    public function getCategory(User $user): int
    {
        $today = new DateTime();
        $age = $today->diff($user->getIdentities()->first()->getBirthDate());

        return (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
    }


    public function getRegistrationTitle(User $user): string
    {
        $title = $this->translator->trans('registration_step.type.default');
        $licence = $user->getSeasonLicence($this->getCurrentSeason());
        $title = 'registration_step.type.';
        if (null !== $licence) {
            if (!$licence->isFinal()) {
                $title .= 'testing';
            } else {
                $category = $licence->getCategory();
                if (null !== $category) {
                    $categories = [
                        Licence::CATEGORY_MINOR => 'minor',
                        Licence::CATEGORY_ADULT => 'adult',
                    ];
                    $title .= $categories[$category];
                } 
            }
        }
        return $this->translator->trans($title);;
    }
}