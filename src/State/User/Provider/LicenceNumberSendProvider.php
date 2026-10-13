<?php

declare(strict_types=1);

namespace App\State\User\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\FlashesView;
use App\Entity\User;

/**
 * @implements TurboStreamProviderInterface<User>
 */
class LicenceNumberSendProvider implements TurboStreamProviderInterface
{
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): FlashesView
    {
        return FlashesView::create($flashMessage->type, $flashMessage->messageKey);
    }
}
