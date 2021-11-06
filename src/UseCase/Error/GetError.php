<?php

namespace App\UseCase\Error;

use App\Entity\LogError;
use App\ViewModel\UserPresenter;
use DateTime;
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

    public function execute(Request $request): LogError
    {
        $exception = $request->attributes->get('exception');
        $logError = new LogError();

        $logError->setUrl($request->getRequestUri())
            ->setErrorMessage($exception->getMessage())
            ->setMessage('Une erreur est survenue !<br>Si le problème persite, contacter le club')
            ->setUserAgent($request->headers->get('user-agent'))
            ->setCreatedAt(new DateTime())
            ->setStatusCode(500)
            ;
     
        if ($exception instanceof ErrorException) {
            $logError->setFileName($exception->getFile())
                ->setLine($exception->getLine())
                ;
        }

        if ($exception instanceof NotFoundHttpException || $exception instanceof AccessDeniedHttpException) {
            $statusCode = $exception->getStatusCode();
            $logError->setStatusCode($statusCode);

            if (403 === $statusCode) {
                $logError->setRoute($exception->getPrevious()->getSubject()->attributes->get('_route'))
                    ->setMessage('Vous n\'avez pas les droits nécessaires pour afficher cette page.');
            }
            if (404 === $statusCode) {
                $logError->setMessage('La page recherchée n\'existe pas.');
            }
        }
        $this->addUser($logError);

        $this->setPersist($logError);

        return $logError;
    }

    private function setPersist(LogError &$logError): void
    {
        $robots = ['Googlebot', 'AdsBot-Google', 'Googlebot-Image', 'bingbot', 'bot', 'ltx71','GoogleImageProxy'];
        $pattern = '#%s#i';
        if (preg_match(sprintf($pattern, implode('|', $robots)), $logError->getUserAgent())) {
            $logError->setPersit(false);
        }
    }

    private function addUser(LogError &$logError): void
    {
        $user = $this->security->getUser();
        if ($user) {
            $logError->setUser($user);
        }
    }
}