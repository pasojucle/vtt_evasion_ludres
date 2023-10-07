<?php

declare(strict_types=1);

namespace App\UseCase\Registration;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Licence;
use App\Entity\User;

class GetStatusWarning
{
    public function __construct(
        private UserDtoTransformer $userDtoTransformer
    )
    {
    }

    public function execute(User $user): string
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);
        $lastLicence = $userDto->lastLicence;
        if ($lastLicence->isSeasonLicence) {
            $licenceStatus = ( in_array($lastLicence->status, [Licence::STATUS_TESTING, Licence::STATUS_VALID])) ? 'validée' : 'téléchargée';
            if ($lastLicence->isFinal) {
                return sprintf('Votre inscription pour la saison %s a été %s.<br>Vous ne pouvez plus la modifier en ligne.</p>', $lastLicence->season, $licenceStatus);
            }

            if ($user->getDoneSessions()->isEmpty() && Licence::CATEGORY_MINOR === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez avoir participé au moins à une sortie du club. </p>', $licenceStatus, $lastLicence->season);
            }

            if ($user->getSessions()->isEmpty() && Licence::CATEGORY_ADULT === $lastLicence->category) {
                return sprintf('Votre inscription aux 3 séances d\'essai a été %s.<br>Pour s\'incrire à la saison %s, vous devez être inscrit au moins à une sortie du club. </p>', $licenceStatus, $lastLicence->season);
            }
        }

        return '';
    }
}
