<?php

declare(strict_types=1);

namespace App\State;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LucideIconProvider
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/config/data/lucide-icons.json')]
        private string $jsonFilePath
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getIconChoices(): array
    {
        if (!file_exists($this->jsonFilePath)) {
            return [];
        }

        $jsonContent = file_get_contents($this->jsonFilePath);
        $data = json_decode($jsonContent, true);

        if (!$data) {
            return [];
        }

        $iconNames = $data['uncategorized'] ?? [];

        if (empty($iconNames)) {
            return [];
        }

        $choices = [];
        foreach ($iconNames as $iconName) {
            $name = 'lucide:' . $iconName;
            $choices[$name] = $name;
        }

        ksort($choices);

        return $choices;
    }
}
