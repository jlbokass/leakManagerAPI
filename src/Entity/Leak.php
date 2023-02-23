<?php

namespace App\Entity;

use App\Repository\LeakRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Since;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LeakRepository::class)]
class Leak
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getLeaks", "getCampaigns"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getLeaks"])]
    #[Since("1.0")]
    #[Assert\NotBlank(message: 'The leak location cannot be blank or null')]
    #[Assert\Length(
        min: 2,
        max: 150,
        minMessage: 'The leak location name must contain at least {{ limit }} characters',
        maxMessage: 'The leak location name must contain a maximum of {{ limit }} characters'
    )]
    private ?string $leakLocation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getLeaks"])]
    #[Since("1.0")]
    private ?string $leakDescription = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getLeaks"])]
    #[Since("1.0")]
    private ?string $leakImageBig = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getLeaks"])]
    #[Since("1.0")]
    private ?string $leakImageSmall = null;

    #[ORM\Column]
    #[Groups(["getLeaks", "getCampaigns"])]
    #[Since("1.0")]
    #[Assert\Positive(message: 'leak number must be positive')]
    private ?int $leakNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getLeaks", "getCampaigns"])]
    #[Assert\Positive(message: 'measuredFlow must be positive')]
    #[Since("1.0")]
    private ?float $measuredFlow = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getLeaks", "getCampaigns"])]
    #[Since("1.0")]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getLeaks"])]
    #[Since("1.0")]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'leaks')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'leaks')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'leaks')]
    #[Groups(["getLeaks", "getCampaigns"])]
    private ?LeakStatus $lmStatus = null;

    #[ORM\ManyToOne(inversedBy: 'leaks')]
    #[Groups(["getLeaks", "getCampaigns"])]
    private ?Severity $severity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["getLeaks", "getCampaigns"])]
    private ?string $comment = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeakLocation(): ?string
    {
        return $this->leakLocation;
    }

    public function setLeakLocation(string $leakLocation): self
    {
        $this->leakLocation = $leakLocation;

        return $this;
    }

    public function getLeakDescription(): ?string
    {
        return $this->leakDescription;
    }

    public function setLeakDescription(?string $leakDescription): self
    {
        $this->leakDescription = $leakDescription;

        return $this;
    }

    public function getLeakImageBig(): ?string
    {
        return $this->leakImageBig;
    }

    public function setLeakImageBig(string $leakImageBig): self
    {
        $this->leakImageBig = $leakImageBig;

        return $this;
    }

    public function getLeakImageSmall(): ?string
    {
        return $this->leakImageSmall;
    }

    public function setLeakImageSmall(string $leakImageSmall): self
    {
        $this->leakImageSmall = $leakImageSmall;

        return $this;
    }

    public function getLeakNumber(): ?int
    {
        return $this->leakNumber;
    }

    public function setLeakNumber(int $leakNumber): self
    {
        $this->leakNumber = $leakNumber;

        return $this;
    }

    public function getMeasuredFlow(): ?float
    {
        return $this->measuredFlow;
    }

    public function setMeasuredFlow(?float $measuredFlow): self
    {
        $this->measuredFlow = $measuredFlow;

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

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getLmStatus(): ?LeakStatus
    {
        return $this->lmStatus;
    }

    public function setLmStatus(?LeakStatus $lmStatus): self
    {
        $this->lmStatus = $lmStatus;

        return $this;
    }

    public function getSeverity(): ?Severity
    {
        return $this->severity;
    }

    public function setSeverity(?Severity $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function lossInCubeMeter(): float
    {
        return $this->measuredFlow * 0.06;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
