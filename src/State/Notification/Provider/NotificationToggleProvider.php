<?php

declare(strict_types=1);

namespace App\State\Notification\Provider;

use App\Dto\DialogModalDto;
use App\Dto\Enum\DialogType;
use App\Entity\Notification;


class NotificationToggleProvider
{
    public function mapToView(Notification $entity): DialogModalDto
    {
        if ($entity->isDisabled()) {
            return new DialogModalDto(
                type: DialogType::SUCCESS,
                title: 'Activation',
                action: 'Activer',
                message: sprintf('Etes vous certain de vouloir activer la pop\'up %s ?', $entity->getTitle()),
                icon: 'lucide:check'
            );
        }
        
        return new DialogModalDto(
            type: DialogType::WARNING,
            title: 'Désactivation',
            action: 'Désactiver',
            message: sprintf('Etes vous certain de vouloir désactiver la pop\'up %s ?', $entity->getTitle()),
            icon: 'lucide:x'
        );
    }
}