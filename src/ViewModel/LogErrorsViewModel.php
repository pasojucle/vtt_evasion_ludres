<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsViewModel
{
    public ?array $logErrors;

    public ?array $tabs;

    public static function fromLogErrors(Paginator $logErrors, array $services): LogErrorsViewModel
    {
        $logErrorsViewModel = [];
        if (!empty($logErrors)) {
            foreach ($logErrors as $logError) {
                $logErrorsViewModel[] = LogErrorViewModel::fromLogError($logError, $services);
            }
        }

        $logErrorsView = new self();
        $logErrorsView->logErrors = $logErrorsViewModel;
        $logErrorsView->tabs = $logErrorsView->getTabs();

        return $logErrorsView;
    }

    private function getTabs()
    {
        return [
            500 => 'Erreur d\'application',
            404 => 'Page inexistantes',
            403 => 'Problème d\'authorisation',
        ];
    }
}
