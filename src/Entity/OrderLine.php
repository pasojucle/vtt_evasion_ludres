<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[Column(type: "integer")]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ManyToOne(targetEntity: OrderHeader::class, inversedBy: "orderLines")]
    #[JoinColumn(nullable: false)]
    private OrderHeader $orderHeader;

    #[ManyToOne(targetEntity: Product::class, inversedBy: "orderLines")]
    #[JoinColumn(nullable: false)]
    private Product $product;

    #[ManyToOne(targetEntity: Size::class)]
    #[JoinColumn(nullable: false)]
    private Size $size;

    #[Column(type: "integer")]
    private int $quantity;

    private $lineId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLineId(int $lineId): self
    {
        $this->lineId = $lineId;

        return $this;
    }

    public function getLineId(): ?int
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
}
