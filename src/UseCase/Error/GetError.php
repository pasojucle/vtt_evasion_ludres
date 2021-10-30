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
        $error['userAgent'] = $request->headers->get('user-agent');
        $error['REMOTE_ADDR'] = $request->server->get('REMOTE_ADDR');
        

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
        $this->addUser($error);

        $error['sendMessage'] = $this->getSend($error);

        return $error;
    }

    private function getSend(array $error): bool
    {
        $sendMessage = true;
        $robots = ['Googlebot', 'AdsBot-Google', 'Googlebot-Image', 'bingbot', 'bot'];
        $pattern = '#%s#i';
        if (preg_match(sprintf($pattern, implode('|', $robots)), $error['userAgent'])) {
            $sendMessage = false;
        }
        if (array_key_exists('statusCode', $error) && 404 === $error['statusCode']) {
            $sendMessage = false;
        }

        return $sendMessage;
    }

    private function addUser(array &$error): void
    {
        $user = $this->security->getUser();
        if ($user) {
            $this->presenter->present($user);
            $user = $this->presenter->viewModel();
            $error['user'] = $user->getMember()['fullName'].' - '. $user->getLicenceNumber();
        }
    }
}