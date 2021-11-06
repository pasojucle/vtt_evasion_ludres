<?php

namespace App\ViewModel;

use App\ViewModel\LogErrorViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsViewModel 
{
    public ?array $logErrors;
    public ?array $tabs;

    public static function fromLogErrors(Paginator $logErrors, array $data): LogErrorsViewModel
    {
        $logErrorsViewModel = [];
        if (!empty($logErrors)) {
            foreach ($logErrors as $logError) {
                $logErrorsViewModel[] = LogErrorViewModel::fromLogError($logError, $data);
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
            403 => 'Problème d\'authorisation'
        ];
    }
}