<?php

namespace App\Entity;

use App\Repository\OrderHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderHeaderRepository::class)
 */
class OrderHeader
{
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_ORDERED = 2;
    public const STATUS_VALIDED= 3;
    public const STATUS_COMPLETED = 4;
    public const STATUS_CANCELED = 9;
    
    public CONST STATUS = [
        self::STATUS_IN_PROGRESS => 'order.in_progress',
        self::STATUS_ORDERED => 'order.ordered',
        self::STATUS_VALIDED => 'order.valided',
        self::STATUS_COMPLETED => 'order.completed',
        self::STATUS_CANCELED => 'order.canceled',
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=OrderLine::class, mappedBy="orderHeader")
     */
    private $orderLines;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orderHeaders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
