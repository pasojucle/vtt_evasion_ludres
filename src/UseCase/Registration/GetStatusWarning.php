<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\User;

class GetStatusWarning
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    public function execute(User $user): string
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $lastLicence = $userDto->lastLicence;
        if ($lastLicence->isSeasonLicence) {
            $licenceStatus = (in_array($lastLicence->state, [LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::YEARLY_FILE_RECEIVED, LicenceStateEnum::YEARLY_FILE_REGISTRED]))
                ? 'validée'
                : 'téléchargée';
      
            if (in_array($lastLicence->state['value'], [LicenceStateEnum::YEARLY_FILE_SUBMITTED, LicenceStateEnum::YEARLY_FILE_RECEIVED, LicenceStateEnum::YEARLY_FILE_REGISTRED ])) {
                return sprintf('Votre inscription pour la saison %s a été %s.<br>Vous ne pouvez plus la modifier en ligne.</p>', $lastLicence->shortSeason, $licenceStatus);
            }
            if ($user->getDoneSessions()->isEmpty() && Licence::CATEGORY_MINOR === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez avoir participé au moins à une sortie du club. </p>', $licenceStatus, $lastLicence->shortSeason);
            }
            if ($user->getSessions()->isEmpty() && Licence::CATEGORY_ADULT === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez être inscrit au moins à une sortie du club. </p>', $licenceStatus, $lastLicence->shortSeason);
            }
            if (LicenceStateEnum::TRIAL_FILE_SUBMITTED === $lastLicence->state['value'] && Licence::CATEGORY_MINOR === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez remettre votre dossier d\'inscription à un encadrant du club et avoir participé au moins à une sortie du club.', $licenceStatus, $lastLicence->shortSeason);
            }
            if (LicenceStateEnum::TRIAL_FILE_SUBMITTED === $lastLicence->state['value'] && Licence::CATEGORY_ADULT === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez remettre votre dossier d\'inscription à un encadrant du club et être inscrit au moins à une sortie du club.', $licenceStatus, $lastLicence->shortSeason);
            }
        }

        return '';
    }
}
