<?php

declare(strict_types=1);

namespace App\Mapper\Notification;

use App\Dto\Enum\PublishStatus;
use App\Dto\View\BadgeView;
use App\Entity\Notification;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationStatusMapper {

    public function __construct(
        private TranslatorInterface $translator,
    ){}

    public function mapToView(Notification $entity, string $toggleStatusId): BadgeView
    {
        $status = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;

        return new BadgeView(
            value: $status->trans($this->translator), 
            variant: $status->variant(),
            toggleStatusId:$toggleStatusId,
        );
    }
}