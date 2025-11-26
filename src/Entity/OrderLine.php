<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\OrderLineStateEnum;
use App\Repository\OrderLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: OrderHeader::class, inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: false)]
    private OrderHeader $orderHeader;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Size::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Size $size;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'OrderLineState', options:['default' => OrderLineStateEnum::IN_STOCK->value])]
    private OrderLineStateEnum $state = OrderLineStateEnum::IN_STOCK;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderHeader(): ?OrderHeader
    {
        return $this->orderHeader;
    }

    public function setOrderHeader(?OrderHeader $orderHeader): self
    {
        $this->orderHeader = $orderHeader;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getSize(): ?Size
    {
        return $this->size;
    }

    public function setSize(?Size $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getState(): OrderLineStateEnum
    {
        return $this->state;
    }

    public function setState(OrderLineStateEnum $state): static
    {
        $this->state = $state;

        return $this;
    }
}
