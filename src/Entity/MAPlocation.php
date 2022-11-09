<?php

namespace App\Entity;

use App\Repository\MAPlocationRepository;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MAPlocationRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class MAPlocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 5)]
    #[Assert\LessThan(90.00001)]
    #[Assert\GreaterThan(-90.00001)]
    private ?float $latitude = null;

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 5)]
    #[Assert\LessThan(180.00001)]
    #[Assert\GreaterThan(-180.00001)]
    private ?float $longitude = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotNull()]
    private ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MAPmap $map = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist()]
    #[ORM\PreUpdate()]
    public function setUpdatedValue()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = str_replace('`', "'", $name);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = str_replace('`', "'", $description);

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = str_replace('`', "'", $icon);

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = str_replace('`', "'", $link);

        return $this;
    }

    public function getMap(): ?MAPmap
    {
        return $this->map;
    }

    public function setMap(?MAPmap $map): self
    {
        $this->map = $map;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
