<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Since;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCampaigns'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    #[Assert\NotBlank(message: 'Society name cannot be blank or null')]
    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'The name of a society must contain at least {{ limit }} characters',
        maxMessage: 'The name of a society must contain a maximum of {{ limit }} characters'
    )]
    private ?string $societyName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    #[Assert\NotBlank(message: 'location cannot be blank or null')]
    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'location must contain at least {{ limit }} characters',
        maxMessage: 'location must contain a maximum of {{ limit }} characters'
    )]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    #[Assert\Positive(message: 'The KWH price should be positive')]
    private ?float $kwhPrice = null;

    #[ORM\Column]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    #[Assert\Positive(message: 'The value should be positive')]
    private ?int $nbrCompressorUseByYear = null;

    #[ORM\Column]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    #[Assert\Positive(message: 'The value should be positive')]
    private ?float $electricityPrice = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['getCampaigns'])]
    #[Since("1.0")]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    #[Groups(["getCampaigns"])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: Leak::class, cascade: ['remove'])]
    #[Groups(["getCampaigns"])]
    private Collection $leaks;

    #[ORM\Column]
    #[Groups(["getCampaigns"])]
    #[Since("1.0")]
    private ?bool $isActive = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->leaks = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSocietyName(): ?string
    {
        return $this->societyName;
    }

    public function setSocietyName(string $societyName): self
    {
        $this->societyName = $societyName;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getKwhPrice(): ?float
    {
        return $this->kwhPrice;
    }

    public function setKwhPrice(float $kwhPrice): self
    {
        $this->kwhPrice = $kwhPrice;

        return $this;
    }

    public function getNbrCompressorUseByYear(): ?int
    {
        return $this->nbrCompressorUseByYear;
    }

    public function setNbrCompressorUseByYear(int $nbrCompressorUseByYear): self
    {
        $this->nbrCompressorUseByYear = $nbrCompressorUseByYear;

        return $this;
    }

    public function getElectricityPrice(): ?float
    {
        return $this->electricityPrice;
    }

    public function setElectricityPrice(float $electricityPrice): self
    {
        $this->electricityPrice = $electricityPrice;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
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
            $leak->setCampaign($this);
        }

        return $this;
    }

    public function removeLeak(Leak $leak): self
    {
        if ($this->leaks->removeElement($leak)) {
            // set the owning side to null (unless already changed)
            if ($leak->getCampaign() === $this) {
                $leak->setCampaign(null);
            }
        }

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
