<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class ExportService
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function exportUsers(array $users): string
    {
        $content = [];
        $row = ['Prénom', 'Nom', 'Mail', 'Date de naissance', 'Numéro de licence', 'Année', '3 séances d\'essai'];
        $content[] = implode(',', $row);

        if (!empty($users)) {
            foreach ($users as $user) {
                $identity = $user->getFirstIdentity();
                $licence = $user->getLastLicence();
                $row = [$identity->getFirstName(), $identity->getName(), $identity->getEmail(), $identity->getBirthDate()->format('d/m/Y'), $user->getLicenceNumber(), $licence->getSeason(), !$licence->isFinal()];
                $content[] = implode(',', $row);
            }
        }

        return implode(PHP_EOL, $content);
    }
}
