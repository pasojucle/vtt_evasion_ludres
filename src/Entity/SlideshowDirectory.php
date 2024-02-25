<?php

namespace App\Entity;

use App\Repository\SlideshowDirectoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlideshowDirectoryRepository::class)]
class SlideshowDirectory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'directory', targetEntity: SlideshowImage::class)]
    private Collection $slideshowImages;

    public function __construct()
    {
        $this->slideshowImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, SlideshowImage>
     */
    public function getSlideshowImages(): Collection
    {
        return $this->slideshowImages;
    }

    public function addSlideshowImage(SlideshowImage $slideshowImage): static
    {
        if (!$this->slideshowImages->contains($slideshowImage)) {
            $this->slideshowImages->add($slideshowImage);
            $slideshowImage->setDirectory($this);
        }

        return $this;
    }

    public function removeSlideshowImage(SlideshowImage $slideshowImage): static
    {
        if ($this->slideshowImages->removeElement($slideshowImage)) {
            // set the owning side to null (unless already changed)
            if ($slideshowImage->getDirectory() === $this) {
                $slideshowImage->setDirectory(null);
            }
        }

        return $this;
    }
}
