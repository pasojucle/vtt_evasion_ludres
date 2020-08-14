<?php

namespace App\Service;

use App\Repository\ParameterRepository;



class ParameterService
{

    private $parameterRepository;
 
    public function __construct(ParameterRepository $parameterRepository) {
 
        $this->parameterRepository = $parameterRepository;
    }
 
    public function getParameter($name) {
        
        $parameter =  $this->parameterRepository->findOneByName($name);

        return (null!== $parameter) ? $parameter->getValue() : null;
    }
}