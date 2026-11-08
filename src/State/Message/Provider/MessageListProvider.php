<?php

declare(strict_types=1);

namespace App\State\Message\Provider;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Contract\Provider\ListProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\AbstractFilter;
use App\Dto\Filter\MessageFilter;
use App\Dto\View\ListView;
use App\Mapper\Message\MessageListMapper;
use App\Mapper\SecondHand\SecondHandListMapper;
use App\Repository\MessageRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\QueryBuilder;

/**
 * @implements ListProviderInterface<MessageFilter>
 */
class MessageListProvider implements ListProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        private MessageRepository $messageRepository,
        private PaginatorService $paginator,
        private MessageListMapper $mapper,
    ) {
    }

    public function getCollection(AbstractFilter $filter, FilterConfigInterface $filterConfig, HandlerContext $context): ListView
    {
        $qb = $this->getQueryBuilder($filter);

        $entities = $this->paginator->paginate(
            $qb,
            $context->page,
            $filter->itemsPerPage ?? PaginatorService::PAGINATOR_PER_PAGE
        );

        return $this->mapper->mapToView(
            $entities,
            $context,
            $filter,
            $filterConfig
        );
    }

    private function getQueryBuilder(MessageFilter $filter): QueryBuilder
    {
        $qb = $this->messageRepository->getMessageQuery();

        if ($filter->section) {
            $this->messageRepository->filterSection($qb, $filter->section);
        }

        if ($filter->label) {
            $this->messageRepository->filterLabel($qb, $filter->label);
        }

        if ($filter->levelType) {
            $this->messageRepository->filterLevelType($qb, $filter->levelType);
        }

        if ($filter->sort) {
            $this->messageRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}
