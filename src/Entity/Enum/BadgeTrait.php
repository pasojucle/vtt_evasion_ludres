<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

trait BadgeTrait
{
    /**
     * Transforme l'instance actuelle de l'Enum en tableau pour la vue
     */
    public function toBadge(TranslatorInterface $translator): ?array
    {
        if ($this->value === 'none' || $this->name === 'NONE') {
            return null;
        }

        return [
            'label' => $this->trans($translator),
            'color' => $this->color(),
        ];
    }
}
