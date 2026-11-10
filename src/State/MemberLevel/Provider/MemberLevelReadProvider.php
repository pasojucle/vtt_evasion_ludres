<?php

declare(strict_types=1);

namespace App\State\MemberLevel\Provider;

use App\Core\Contract\Provider\ComponentProviderInterface;
use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\MemberLevel\MemberLevelView;
use App\Dto\View\SheetView;
use App\Entity\Member;
use App\Mapper\MemberLevel\MemberLevelReadMapper;

/**
 * @implements ComponentProviderInterface<Member>
 */
class MemberLevelReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private MemberLevelReadMapper $memberLevelReadMapper,
    ) {
    }

    public function getView(object $entity, ?HandlerContext $context = null): MemberLevelView
    {
        return $this->memberLevelReadMapper->mapToView($entity);
    }
}
