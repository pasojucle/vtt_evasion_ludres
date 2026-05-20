<?php

declare(strict_types=1);

namespace App\State\BoardRole\Provider;

use App\Dto\DialogModalDto;
use App\Entity\BoardRole;
use App\State\Base\Provider\DestructiveModalMapper;

class BoardRoleDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(BoardRole  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf($label, $entity->$field()));
    }
}