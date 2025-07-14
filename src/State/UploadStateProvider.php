<?php

namespace App\State;

use App\ApiResource\UploadFile;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UploadStateProvider implements ProviderInterface
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    )
    {
        
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filename = $uriVariables['filename'];
        $path = [$this->parameterBag->get('project_directory'),  UploadFile::PATH, $filename];
        $file = implode(DIRECTORY_SEPARATOR, $path);
        if (!file_exists($file)) {
            throw new NotFoundHttpException(Sprintf('Le fichier %s n\'existe pas', $filename));
        }
 
        $response =  new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}
