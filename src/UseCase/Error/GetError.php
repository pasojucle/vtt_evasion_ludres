<?php

namespace App\UseCase\Error;

use App\ViewModel\UserPresenter;
use ErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GetError
{
    private Security $security;
    private UserPresenter $presenter;

    public function __construct(
        Security $security,
        UserPresenter $presenter
    )
    {
        $this->security = $security;
        $this->presenter = $presenter;
    }

    public function execute(Request $request): array
    {
        $exception = $request->attributes->get('exception');
        $error = [];
        $error['requestUri'] = $request->getRequestUri();
        $error['message'] = $exception->getMessage();
        $error['humanMessage'] = 'Une erreur est survenue !<br>Si le problème persite, contacter le club';

        if ($exception instanceof ErrorException) {
            $error['file'] = $exception->getFile();
            $error['line'] = $exception->getLine();
        }

        if ($exception instanceof NotFoundHttpException || $exception instanceof AccessDeniedHttpException) {
            $statusCode = $exception->getStatusCode();
            $error['statusCode'] = $statusCode;
            if (403 === $statusCode) {
                $error['humanMessage'] = 'Vous n\'avez pas les droits nécessaires pour afficher cette page.';
            }
            if (404 === $statusCode) {
                $error['humanMessage'] = 'La page recherchée n\'existe pas.';
            }
        }
        $user = $this->security->getUser();
        if ($user) {
            $this->presenter->present($user);
            $user = $this->presenter->viewModel();
            $error['user'] = $user->getMember()['fullName'].' - '. $user->getLicenceNumber();
        }

        return $error;
    }
}