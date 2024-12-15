<?php

namespace App\Entity;

use App\Repository\SecondHandRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecondHandRepository::class)]
class SecondHand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(type: 'integer')]
    private int $price = 0;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'secondHands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\Column]
    private bool $deleted = false;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private bool $disabled = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validedAt = null;

    /**
     * @var Collection<int, SecondHandImage>
     */
    #[ORM\OneToMany(targetEntity: SecondHandImage::class, mappedBy: 'secondHand', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getValidedAt(): ?\DateTimeImmutable
    {
        return $this->validedAt;
    }

    public function setValidedAt(?\DateTimeImmutable $validedAt): static
    {
        $this->validedAt = $validedAt;

        return $this;
    }

    /**
     * @return Collection<int, SecondHandImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(SecondHandImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setSecondHand($this);
        }

        return $this;
    }

    public function removeImage(SecondHandImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getSecondHand() === $this) {
                $image->setSecondHand(null);
            }
        }

        return $this;
    }
}
