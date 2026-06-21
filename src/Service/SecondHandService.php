<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SecondHand;
use Symfony\Component\Workflow\WorkflowInterface;

class SecondHandService
{
        public function __construct(
        private WorkflowInterface $secondHandStateMachine,
    ) {
    }

    public function applyTransition(SecondHand $secondHand, string $transition): bool
    {
        if ($this->secondHandStateMachine->can($secondHand, $transition)) {
            $this->secondHandStateMachine->apply($secondHand, $transition);
            return true;
        }
        
        return false;
    }
}