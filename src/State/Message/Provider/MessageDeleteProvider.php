<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Message;
use App\Mapper\DestructiveModalMapper;

class MessageDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Message  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le message  <b>%s</b> ?', $entity->getLabel()));
    }
}