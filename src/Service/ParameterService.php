<?php

namespace App\Service;

use App\Repository\ParameterRepository;
use Doctrine\Common\Collections\ArrayCollection;



class ParameterService
{

    private $parameterRepository;
    private $fileUploader;
 
    public function __construct(ParameterRepository $parameterRepository, FileUploader $fileUploader) {
 
        $this->parameterRepository = $parameterRepository;
        $this->fileUploader = $fileUploader;
    }
 
    public function getParameter($name) {
        
        $parameter =  $this->parameterRepository->findOneByName($name);

        return (null!== $parameter) ? $parameter->getValue() : null;
    }

    public function getEncryption(ArrayCollection $parameters)
    {
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                if ('ENCRYPTION' === $parameter->getName()){
                    return (bool) $parameter->getValue();
                }
            }
        }
        return null;
    }

    public function uploadFiles(ArrayCollection &$parameters, array $files)
    {
        if (!empty($parameters)) {
            foreach ($parameters as $key => $parameter) {
                if ('image' === $parameter->getType()){
                    $value = $parameter->getValue();
                    $filename = $value['filename'];
                    $file = $files[$key]['value']['file'];
                    if (null !== $file) {
                        $filename = $this->fileUploader->upload($file, null, 32);
                    }
                    $parameter->setValue($filename);
                }
            }
        }
    }
}