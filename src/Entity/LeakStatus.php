<?php

namespace App\Entity;

use App\Repository\LeakStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LeakStatusRepository::class)]
class LeakStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getLeaksStatus", "getLeaks"])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Groups(["getLeaksStatus", "getLeaks", "getCampaigns"])]
    private ?string $statusName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getLeaksStatus"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getLeaksStatus"])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'leaksStatus')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'lmStatus', targetEntity: Leak::class)]
    private Collection $leaks;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->leaks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusName(): ?string
    {
        return $this->statusName;
    }

    public function setStatusName(string $statusName): self
    {
        $this->statusName = $statusName;

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
            $leak->setLmStatus($this);
        }

        return $this;
    }

    public function removeLeak(Leak $leak): self
    {
        if ($this->leaks->removeElement($leak)) {
            // set the owning side to null (unless already changed)
            if ($leak->getLmStatus() === $this) {
                $leak->setLmStatus(null);
            }
        }

        return $this;
    }
}
