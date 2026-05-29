<?php

declare(strict_types=1);

namespace App\Mapper\User;

use App\Service\IdentityService;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAdminListExportMapper
{
    private const CSV_SEPARATOR = ",";
    
    public function __construct(
        private IdentityService $identityService,
        private TranslatorInterface $translator,
    ) {
    }

    public function streamToCsv(array $entities): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fp = fopen('php://output', 'w');
        
        $headers = ['Numéro de licence', 'Nom', 'Prénom', 'Groupe ou Niveau', 'Mail contact principal', 'Date de naissance', 'Lieu de naissance', 'Département de naissance', 'Pays de naissance', 'Année', '3 séances d\'essai'];
        fputcsv($fp, $headers, self::CSV_SEPARATOR);

        foreach ($entities as $entity) {
            $identity = $entity->getIdentity();
            [$birthPlace, $birthDepartment, $birthCountry] = $this->identityService->getBirthplace($identity);
            $level = $entity->getLevel();
            $licence = $entity->getLastLicence();
            $season = $licence->getSeason();
            $licenceState = $licence->getState();
            $mainRow = [
                $entity->getLicenceNumber(),
                $identity->getName(),
                $identity->getFirstName(),
                $level->getTitle(),
                $entity->getContactEmail(),
                $identity->getBirthDate()?->format('d/m/Y') ?? '-',
                $birthPlace,
                $birthDepartment,
                $birthCountry,
                sprintf('%s - %s', (string) ($season - 1), (string) $season),
                $licenceState->isYearly() ? 0 : 1,
                $licenceState->trans($this->translator)
            ];
            
            fputcsv($fp, $mainRow, self::CSV_SEPARATOR);
            flush();
        }

        fclose($fp);
    }
}
