<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Entity\Skill;

class SkillListExportMapper
{
    private const CSV_SEPARATOR = ",";

    /**
     * @param Skill[] $skills
     */
    public function streamToCsv(array $skills): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fp = fopen('php://output', 'w');
        
        $this->addExportHeader($fp);
        fputcsv($fp, [], self::CSV_SEPARATOR);
        $this->addExportContent($skills, $fp);

        fclose($fp);
    }

    private function addExportHeader($fp): void
    {
        $row = ['Descriptif', 'Catégorie', 'Niveau'];
        fputcsv($fp, $row, self::CSV_SEPARATOR);
    }

    /**
     * @param Skill[] $skills
     */
    private function addExportContent(array $skills, $fp): void
    {
        foreach ($skills as $skill) {
            $row = [
                sprintf('"%s"', $skill->getContent()),
                $skill->getCategory()->getName(),
                $skill->getLevel()->getTitle()
            ];

            fputcsv($fp, $row, self::CSV_SEPARATOR);
            flush();
        }
    }
}
