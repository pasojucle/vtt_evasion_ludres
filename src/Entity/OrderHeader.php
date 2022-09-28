<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderHeaderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(repositoryClass: OrderHeaderRepository::class)]
class OrderHeader
{
    public const STATUS_IN_PROGRESS = 1;

    public const STATUS_ORDERED = 2;

    public const STATUS_VALIDED = 3;

    public const STATUS_COMPLETED = 4;

    public const STATUS_CANCELED = 9;

    public const STATUS = [
        self::STATUS_IN_PROGRESS => 'order.in_progress',
        self::STATUS_ORDERED => 'order.ordered',
        self::STATUS_VALIDED => 'order.valided',
        self::STATUS_COMPLETED => 'order.completed',
        self::STATUS_CANCELED => 'order.canceled',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[OneToMany(targetEntity: OrderLine::class, mappedBy: 'orderHeader')]
    private Collection $orderLines;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'orderHeaders')]
    #[JoinColumn(nullable: false)]
    private User $user;

    #[Column(type: 'integer')]
    private int $status;

    #[Column(type: 'datetime')]
    private DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->orderLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|OrderLine[]
     */
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    public function addOrderLine(OrderLine $orderLine): self
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines[] = $orderLine;
            $orderLine->setOrderHeader($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): self
    {
        if ($this->orderLines->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getOrderHeader() === $this) {
                $orderLine->setOrderHeader(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
