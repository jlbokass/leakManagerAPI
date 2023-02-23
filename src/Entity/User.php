<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Since;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers", "getCampaigns"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(["getUsers", "getCampaigns"])]
    #[Since("1.0")]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[Assert\Unique(message: 'this email is already used')]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["getUsers"])]
    #[Assert\Json(
        message: "You've entered an invalid Json."
    )]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Since("1.0")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Your password must be at least {{ limit }} characters long',
        maxMessage: 'Your password cannot be longer than {{ limit }} characters',
    )]
    #[SecurityAssert\UserPassword(
        message: 'Wrong value for your current password',
    )]
    private ?string $password = null;

    #[ORM\Column(length: 150)]
    #[Since("1.0")]
    #[Groups(["getUsers", "getCampaigns"])]
    #[Assert\NotBlank(message: 'firstname cannot be blank or null')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Your firstname must be at least {{ limit }} characters long',
        maxMessage: 'Your firstname cannot be longer than {{ limit }} characters',
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    #[Groups(["getUsers", "getCampaigns"])]
    #[Since("1.0")]
    #[Assert\NotBlank(message: 'lastname cannot be blank or null')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Your lastname must be at least {{ limit }} characters long',
        maxMessage: 'Your lastname cannot be longer than {{ limit }} characters',
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(["getUsers", "getCampaigns"])]
    #[Since("1.0")]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getUsers"])]
    #[Since("1.0")]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getUsers"])]
    #[Since("1.0")]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["getUsers"])]
    private ?Agency $agency = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Gaz::class)]
    private Collection $gazs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Severity::class)]
    private Collection $severities;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Campaign::class)]
    private Collection $campaigns;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: LeakStatus::class)]
    private Collection $leaksStatus;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Leak::class)]
    private Collection $leaks;

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
        $this->gazs = new ArrayCollection();
        $this->severities = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->leaksStatus = new ArrayCollection();
        $this->leaks = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

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

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    public function setAgency(?Agency $agency): self
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * @return Collection<int, Gaz>
     */
    public function getGazs(): Collection
    {
        return $this->gazs;
    }

    public function addGaz(Gaz $gaz): self
    {
        if (!$this->gazs->contains($gaz)) {
            $this->gazs->add($gaz);
            $gaz->setUser($this);
        }

        return $this;
    }

    public function removeGaz(Gaz $gaz): self
    {
        if ($this->gazs->removeElement($gaz)) {
            // set the owning side to null (unless already changed)
            if ($gaz->getUser() === $this) {
                $gaz->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Severity>
     */
    public function getSeverities(): Collection
    {
        return $this->severities;
    }

    public function addSeverity(Severity $severity): self
    {
        if (!$this->severities->contains($severity)) {
            $this->severities->add($severity);
            $severity->setUser($this);
        }

        return $this;
    }

    public function removeSeverity(Severity $severity): self
    {
        if ($this->severities->removeElement($severity)) {
            // set the owning side to null (unless already changed)
            if ($severity->getUser() === $this) {
                $severity->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    public function addCampaign(Campaign $campaign): self
    {
        if (!$this->campaigns->contains($campaign)) {
            $this->campaigns->add($campaign);
            $campaign->setUser($this);
        }

        return $this;
    }

    public function removeCampaign(Campaign $campaign): self
    {
        if ($this->campaigns->removeElement($campaign)) {
            // set the owning side to null (unless already changed)
            if ($campaign->getUser() === $this) {
                $campaign->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LeakStatus>
     */
    public function getLeaksStatus(): Collection
    {
        return $this->leaksStatus;
    }

    public function addLeaksStatus(LeakStatus $leaksStatus): self
    {
        if (!$this->leaksStatus->contains($leaksStatus)) {
            $this->leaksStatus->add($leaksStatus);
            $leaksStatus->setUser($this);
        }

        return $this;
    }

    public function removeLeaksStatus(LeakStatus $leaksStatus): self
    {
        if ($this->leaksStatus->removeElement($leaksStatus)) {
            // set the owning side to null (unless already changed)
            if ($leaksStatus->getUser() === $this) {
                $leaksStatus->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Leak>
     */
    public function getLeaks(): Collection
    {
        return $this->leaks;
    }

    public function addLeak(Leak $leak): self
    {
        if (!$this->leaks->contains($leak)) {
            $this->leaks->add($leak);
            $leak->setUser($this);
        }

        return $this;
    }

    public function removeLeak(Leak $leak): self
    {
        if ($this->leaks->removeElement($leak)) {
            // set the owning side to null (unless already changed)
            if ($leak->getUser() === $this) {
                $leak->setUser(null);
            }
        }

        return $this;
    }
}
