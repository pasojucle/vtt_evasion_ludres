<?php

namespace App\ViewModel;

use App\Entity\LogError;

class LogErrorViewModel extends AbstractViewModel
{
    public ?int $id;
    public ?string $createdAt;
    public ?int $statusCode;
    public ?string $errorMessage;
    public ?string $message;
    public ?string $userAgent;
    public ?string $route;
    public ?string $url;
    public ?UserViewModel $user;

    public static function fromLogError(LogError $logError, array $services)
    {
        $logErrorView = new self();
        $logErrorView->id = $logError->getId();
        $createdAt = $logError->getCreatedAt();
        $logErrorView->createdAt = $createdAt->format('d/m/Y H:i:s');
        $logErrorView->statusCode = $logError->getStatusCode();
        $logErrorView->errorMessage = $logError->getErrorMessage();
        $logErrorView->message = $logError->getMessage();
        $logErrorView->userAgent = $logError->getUserAgent();
        $logErrorView->route = $logError->getRoute();
        $logErrorView->url = $logError->getUrl();
        $logErrorView->fileName = $logError->getFileName();
        $logErrorView->line = $logError->getLine();
        $logErrorView->user = ($logError->getUser())
            ? UserViewModel::fromUser($logError->getUser(),  $services)
            : null;

        return $logErrorView;
    }
}