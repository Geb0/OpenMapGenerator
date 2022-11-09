<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\EntityListeners(['App\EntityListener\UserListener'])]
#[UniqueEntity('email')]
#[UniqueEntity('name')]
#[ORM\HasLifecycleCallbacks()]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3)]
    private ?string $name = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[Assert\Length(min: 5, max: 180)]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    private array $roles = [];

    private ?string $plainPassword = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: MAPmap::class, orphanRemoval: true)]
    private Collection $maps;

    #[ORM\Column(length: 2)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recoverkey = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->maps = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->email = str_replace('`', "'", $email);

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getActualPassword(): ?string
    {
        return $this->actualPassword;
    }

    public function setActualPassword(string $actualPassword): self
    {
        $this->actualPassword = $actualPassword;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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

    /**
     * @return Collection<int, MAPmap>
     */
    public function getMaps(): Collection
    {
        return $this->maps;
    }

    public function addMap(MAPmap $map): self
    {
        if (!$this->maps->contains($map)) {

            $this->maps->add($map);
            $map->setUser($this);
        }

        return $this;
    }

    public function removeMap(MAPmap $map): self
    {
        if ($this->maps->removeElement($map)) {

            // set the owning side to null (unless already changed)
            if ($map->getUser() === $this) {

                $map->setUser(null);
            }
        }

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getRecoverkey(): ?string
    {
        return $this->recoverkey;
    }

    public function setRecoverkey(?string $recoverkey): self
    {
        $this->recoverkey = $recoverkey;

        return $this;
    }
}
