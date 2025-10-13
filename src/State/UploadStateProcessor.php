<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UploadFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private SluggerInterface $slugger,
    ) {
    }

    public function process(mixed $uploadedFile, Operation $operation, array $uriVariables = [], array $context = []): ?UploadFile
    {
        if ($operation instanceof Post) {
            return $this->uploadFile($uploadedFile);
        }

        return null;
    }

    private function uploadFile(UploadFile $uploadFile): UploadFile
    {
        /** @var UploadedFile $file */
        $file = $uploadFile->file;
        if (!$file) {
            throw new BadRequestHttpException('Un problème est survenu à l\'envoi du fichier');
        }
        $uploadFile->filename = sprintf('%s.%s', $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), $file->guessExtension());

        /** @var string $prrojectDirectoy */
        $prrojectDirectoy = $this->parameterBag->get('project_directory') ?? '';
        $directories = [$prrojectDirectoy,  UploadFile::PATH];
        $file->move(implode(DIRECTORY_SEPARATOR, $directories), $uploadFile->filename);
        
        return $uploadFile;
    }
}
