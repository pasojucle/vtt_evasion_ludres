<?php

namespace App\State;

use App\Entity\Parameter;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ParameterStateProcessor implements ProcessorInterface
{
    public function __construct(
        private PersistProcessor $persistProcessor,
        private ParameterBagInterface $parameterBag
    )
    {
        
    }

    public function process(mixed $parameter, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($operation instanceof Post) {
            $this->uploadFile($parameter);
        }

        $this->persistProcessor->process($parameter, $operation, $uriVariables, $context);
    }

    private function uploadFile(Parameter $parameter): void
    {
        /** @var UploadedFile $file */
        $file = $parameter->file;
        dump($file);

        if ($file) {
            $filename = sprintF('favicon.%s', $file->guessExtension());
            $directories = [$this->parameterBag->get('project_directory'), 'public', 'uploads'];
            dump(get_current_user());
            $debug = $file->move(implode(DIRECTORY_SEPARATOR, $directories), $filename);
            $parameter->setValue($filename);
            dump($parameter, $debug);
        }
    }
}
