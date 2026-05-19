<?php

declare(strict_types=1);

namespace App\State\Notification\Provider;

use App\Dto\Enum\NotificationVisibility;
use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\NotificationFilter;
use App\Dto\ListDto;
use App\Mapper\Notification\NotificationAdminListMapper;
use App\Repository\NotificationRepository;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use Doctrine\ORM\QueryBuilder;

class NotificationAdminListProvider
{
    use FilterHydratorTrait;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private PaginatorService $paginator,
        private NotificationAdminListMapper $mapper,
    ) {

    }
    public function getCollection(NotificationFilter $filter,  FilterConfigInterface $filterConfig, string $route, ?int $currentPage = 1): ListDto
    {
        $entities = $this->paginator->paginate(
            $this->getQueryBuilder($filter),
            $currentPage,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE

        );

        return $this->mapper->mapToView(
            $entities, 
            $route, 
            $currentPage, 
            $filter,
            $filterConfig
        );
    }


    public function getQueryBuilder(NotificationFilter $filter): QueryBuilder
    {
        $qb = $this->notificationRepository->findNotificationQuery();

        if ($filter->status) {
            $this->notificationRepository->filterDisabled($qb, $filter->status);
        }

        if ($filter->restriction) {
            $this->notificationRepository->filterHasAge($qb);
        }

        if ($filter->visibility) {
            $this->notificationRepository->filterIsPublic($qb, $filter->visibility === NotificationVisibility::PUBLIC);
        }

        if ($filter->sort) {
            $this->notificationRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
