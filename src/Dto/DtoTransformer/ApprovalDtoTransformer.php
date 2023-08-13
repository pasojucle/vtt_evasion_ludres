<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\User;
use App\Dto\ApprovalDto;
use App\Entity\Approval;
use function Symfony\Component\String\u;
use Doctrine\Common\Collections\Collection;

class ApprovalDtoTransformer
{   
    public function fromEntity(Approval $approval, ?array $changes = null): ApprovalDto
    {
        $approvalDto = new ApprovalDto();
        $approvalDto->name = u(str_replace('approval.', '', User::APPROVALS[$approval->getType()]))->camel()->toString();
        $approvalDto->value = $approval->getValue();
        $approvalDto->toString = ($approval->getValue()) ? 'autorise' : 'n\'autorise pas';
        $approvalDto->toHtml = $this->toHtml($approval->getType(), $approval->getValue());


        if ($changes) {
            $this->formatChanges($changes, $approvalDto);
        }

        return $approvalDto;
    }

    public function fromEntities(Collection|array $approvalEntities,  ?array $changes = null): array
    {
        $approvals = [];
        foreach($approvalEntities as $approvalEntity) {
            $approval = $this->fromEntity($approvalEntity, $changes);
            $approvals[$approval->name] = $approval;
        }

        return $approvals;
    }

    private function toHtml(int $type, bool $value): array
    {
        return match($type) {
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

    private function formatChanges(array $changes, ApprovalDto &$approvalDto): void
    {
        if (array_key_exists('Approval', $changes)) {

            $approvalDto->toString = sprintf('<b>%s</b>', $approvalDto->toString); 
            $approvalDto->toHtml['message'] = sprintf('<b>%s</b>', $approvalDto->toHtml['message']); 

        }
    }
}