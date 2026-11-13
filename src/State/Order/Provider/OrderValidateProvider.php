<?php

declare(strict_types=1);

namespace App\State\Order\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Order\OrderSheetView;
use App\Entity\OrderHeader;
use App\Mapper\Order\OrderValidateMapper;

/**
 * @implements FormComponentProviderInterface<OrderHeader>
 */
class OrderValidateProvider implements FormComponentProviderInterface
{
    public function __construct(
        private OrderValidateMapper $mapper,
    ){}
    public function getView(object $data, ?HandlerContext $context = null): OrderSheetView
    {
        return $this->mapper->mapToView($data, $context);
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'id' => $data->getId(),
            'status' => $data->getStatus(),
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
                'data-turbo-frame' =>'_top',
            ]
        ];
    }
}
