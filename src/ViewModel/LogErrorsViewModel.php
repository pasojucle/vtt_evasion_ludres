<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsViewModel
{
    public ?array $logErrors = [];

    public array $tabs = [
        500 => 'Erreur d\'application',
        404 => 'Page inexistantes',
        403 => 'Problème d\'authorisation',
    ];

    public static function fromLogErrors(Paginator $logErrors, ServicesPresenter $services): LogErrorsViewModel
    {
        $logErrorsViewModel = [];
        if (0 !== $logErrors->count()) {
            foreach ($logErrors as $logError) {
                $logErrorsViewModel[] = LogErrorViewModel::fromLogError($logError, $services);
            }
        }

        $logErrorsView = new self();
        $logErrorsView->logErrors = $logErrorsViewModel;
   

        return $logErrorsView;
    }
}
