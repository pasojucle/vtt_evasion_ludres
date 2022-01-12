<?php

namespace App\Controller;

use App\UseCase\FormValidator\Validate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class FormValidatorController extends AbstractController
{
    /**
     * @Route("form/validator", name="form_validator", options={"expose"=true},)
     */
     public function formValidator(
        Request $request, 
        Validate $validate
    )
    {
        
        return new JsonResponse($validate->execute($request));
    }
}