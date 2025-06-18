<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class ParameterStateProcessor implements ProcessorInterface
{
    public function __construct(
        private PersistProcessor $persistProcessor,
    )
    {
        
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        dump($data);
        $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
