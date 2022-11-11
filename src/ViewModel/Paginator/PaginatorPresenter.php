<?php

declare(strict_types=1);

namespace App\ViewModel\Paginator;

use App\ViewModel\AbstractPresenter;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Paginator $paginator, array $filters = [], ?string $targetRoute = null): void
    {
        if (null !== $paginator) {
            $this->viewModel = PaginatorViewModel::fromPaginator($paginator, $filters, $targetRoute, $this->services);
        } else {
            $this->viewModel = new PaginatorViewModel();
        }
    }

    public function viewModel(): PaginatorViewModel
    {
        return $this->viewModel;
    }
}
