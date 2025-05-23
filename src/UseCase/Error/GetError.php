<?php

declare(strict_types=1);

namespace App\UseCase\Error;

use App\Entity\LogError;
use App\Entity\User;
use App\Service\ParameterService;
use DateTime;
use ErrorException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Error\RuntimeError;

class GetError
{
    public function __construct(
        private Security $security,
        private ParameterService $parameterService,
    ) {
    }

    public function execute(Request $request): LogError
    {
        $exception = $request->attributes->get('exception');
        $logError = new LogError();

        $logError->setUrl($request->getRequestUri())
            ->setErrorMessage($exception->getMessage() . ' / ' . get_class($exception))
            ->setMessage('Une erreur est survenue !<br>Veuillez réessayer plus tard')
            ->setCreatedAt(new DateTime())
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
            ;
        $userAgent = $request->headers->get('user-agent');
        if ($userAgent) {
            $logError->setUserAgent($userAgent);
        }

        if ($exception instanceof ErrorException || $exception instanceof RuntimeError) {
            $logError->setFileName($exception->getFile())
                ->setLine($exception->getLine())
                ;
        }

        if ($exception instanceof NotFoundHttpException || $exception instanceof AccessDeniedHttpException) {
            $statusCode = $exception->getStatusCode();
            $logError->setStatusCode($statusCode);

            if (Response::HTTP_FORBIDDEN === $statusCode) {
                /** @var AccessDeniedException  $previousExceptions */
                $previousExceptions = $exception->getPrevious();
                $route = (!$previousExceptions->getSubject() instanceof User) ? $previousExceptions->getSubject()?->attributes?->get('_route') : null;
                $logError->setRoute($route)
                    ->setMessage('Vous n\'avez pas les droits nécessaires pour afficher cette page.')
                ;
            }
            if (Response::HTTP_NOT_FOUND === $statusCode) {
                $logError->setMessage('La page recherchée n\'existe pas.');
            }
        }
        if (Response::HTTP_NOT_FOUND !== $logError->getStatusCode()) {
            $this->addUser($logError);
        }
        
        $this->setPersist($logError);

        return $logError;
    }

    private function setPersist(LogError &$logError): void
    {
        $robots = $this->parameterService->getParameterByName('ERROR_USER_AGENT_IGNORE');
        $pattern = '#%s#i';
        if ($logError->getUserAgent() && 1 === preg_match(sprintf($pattern, implode('|', $robots)), $logError->getUserAgent())) {
            $logError->setPersist(false);
        }

        $url = $this->parameterService->getParameterByName('ERROR_URL_IGNORE');
        $pattern = '#%s#i';
        if (1 === preg_match(sprintf($pattern, implode('|', $url)), $logError->getUrl())) {
            $logError->setPersist(false);
        }
    }

    public function addUser(LogError &$logError): void
    {
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user) {
            $logError->setUserId($user->getId());
        }
    }
}
