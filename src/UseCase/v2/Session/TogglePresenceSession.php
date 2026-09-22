<?php

declare(strict_types=1);

namespace App\UseCase\v2\Session;

use App\Dto\Service\OperationResult;
use App\Entity\Session;
use App\Event\SessionPresenceToggledEvent;
use App\Repository\Interface\SessionRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class TogglePresenceSession
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(
        Session $session,
    ): OperationResult {

        $session->setIsPresent(!$session->isPresent());
        $this->sessionRepository->save($session);

        $this->eventDispatcher->dispatch(new SessionPresenceToggledEvent($session));

        return OperationResult::success();
    }
}
