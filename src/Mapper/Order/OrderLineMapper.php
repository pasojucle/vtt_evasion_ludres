<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Dto\View\BadgeView;
use App\Dto\View\Order\OrderLineView;
use App\Dto\View\Product\SellingPriceView;
use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\Member;
use App\Entity\OrderLine;
use App\Service\FileLocation\ProductFileLocation;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderLineMapper
{
    public function __construct(
        private ProductFileLocation $location,
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private TranslatorInterface $translator,
    ){}

    public function mapToView(
        OrderLine $line, 
        Member $member,
        bool $isEditable,
        string $targetUrl
    ): OrderLineView {
        $product = $line->getProduct();
        $directory = $this->location->getBaseDirectoryName();
        $sellingPrice = $product->getSellingPrice($member);
        $status = $line->getState();

        return new OrderLineView(
            productName: $product->getName(),
            productPartNumber: $product->getRef(),
            productUrl: $this->urlContextService->generateUrl(
                'admin_product_list', 
                ['partNumber' => $product->getRef()], 
                $targetUrl
            ),
            productPathName: $this->urlGenerator->generate('get_data_file', [
                'directory' => $directory,
                'filename' => $product->getFilename()
            ]),
            size: $line->getSize()->getName(),
            quantity: $line->getQuantity(),
            price: SellingPriceView::create($sellingPrice),
            amount: new BadgeView(
                number_format($line->getAmount(), 2) . ' €',
            ),
            disable: OrderLineStateEnum::UNAVAILABLE === $line->getState(),
            status: $isEditable 
                ? null
                :new BadgeView(
                    $status->trans($this->translator),
                    $status->variant()
                ),
        );
    }
}