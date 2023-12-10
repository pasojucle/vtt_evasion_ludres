<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\ApprovalDto;
use App\Entity\Approval;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use function Symfony\Component\String\u;

class ApprovalDtoTransformer
{
    public function fromEntity(?Approval $approval): ApprovalDto
    {
        $approvalDto = new ApprovalDto();
        if ($approval) {
            $approvalDto->name = u(str_replace('approval.', '', User::APPROVALS[$approval->getType()]))->camel()->toString();
            $approvalDto->value = $approval->getValue();
            $approvalDto->toString = ($approval->getValue()) ? 'autorise' : 'n\'autorise pas';
            $approvalDto->toHtml = $this->toHtml($approval->getType(), $approval->getValue());
        }

        return $approvalDto;
    }

    public function fromEntities(Collection|array $approvalEntities): array
    {
        $approvals = [];
        foreach ($approvalEntities as $approvalEntity) {
            $approval = $this->fromEntity($approvalEntity);
            $approvals[$approval->name] = $approval;
        }

        return $approvals;
    }

    private function toHtml(int $type, ?bool $value): array
    {
        return match ($type) {
            User::APPROVAL_GOING_HOME_ALONE => ($value)
            ? [
                'class' => ['color' => 'success', 'icon' => '<i class="fa-solid fa-house-circle-check"></i>'],
                'message' => 'Autorisé à rentrer seul',
            ]
            : [
                'class' => ['color' => 'alert-danger', 'icon' => '<i class="fa-solid fa-house-circle-xmark"></i>'],
                'message' => 'Pas autorisé à rentrer seul',
            ],
            User::APPROVAL_RIGHT_TO_THE_IMAGE => ($value)
            ? [
                'class' => ['color' => 'success', 'icon' => '<i class="fa-solid fa-camera"></i>'],
                'message' => 'Autorise le club à utiliser mon image',
            ]
            : [
                'class' => ['color' => 'alert-danger', 'icon' => '<i class="fa-solid fa-slash fa-camera"></i>'],
                'message' => 'N\'autorise pas le club à utiliser mon image',
            ],
            default => []
        };
    }
}
