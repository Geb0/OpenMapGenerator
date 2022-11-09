<?php

namespace App\Entity;

use App\Repository\MAPmapRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MAPmapRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class MAPmap
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull()]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $private = null;

    #[ORM\Column(length: 30)]
    private ?string $password = null;

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 5)]
    #[Assert\LessThan(90.00001)]
    #[Assert\GreaterThan(-90.00001)]
    private ?float $latitude = 48.86626;

    #[ORM\Column(type: Types::FLOAT, precision: 10, scale: 5)]
    #[Assert\LessThan(180.00001)]
    #[Assert\GreaterThan(-180.00001)]
    private ?float $longitude = 2.39944;

    #[ORM\Column(type: Types::INTEGER, precision: 2, scale: 0)]
    #[Assert\NotNull()]
    #[Assert\LessThan(21)]
    #[Assert\GreaterThan(-1)]
    private ?int $zoom = 4;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'maps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'map', targetEntity: MAPlocation::class, orphanRemoval: true)]
    private Collection $locations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->locations = new ArrayCollection();
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

    public function setDescription(string $description): self
    {
        $this->description = str_replace('`', "'", $description);

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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

    public function getZoom(): ?int
    {
        return $this->zoom;
    }

    public function setZoom(int $zoom): self
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    /**
     * @return Collection<int, MAPlocation>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(MAPlocation $location): self
    {
        if (!$this->locations->contains($location)) {

            $this->locations->add($location);
            $location->setMap($this);
        }

        return $this;
    }

    public function removeLocation(MAPlocation $location): self
    {
        if ($this->locations->removeElement($location)) {

            if ($location->getMap() === $this) {

                $location->setMap(null);
            }
        }

        return $this;
    }
}
