<?php

declare(strict_types=1);

namespace App\Controller;

use App\UseCase\FormValidator\Validate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormValidatorController extends AbstractController
{
    /**
     * @Route("form/validator", name="form_validator", options={"expose"=true},)
     */
    public function formValidator(
        Request $request,
        Validate $validate
    ) {
        return new JsonResponse($validate->execute($request));
    }
}
