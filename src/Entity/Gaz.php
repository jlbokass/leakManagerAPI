<?php

namespace App\Entity;

use App\Repository\GazRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GazRepository::class)]
class Gaz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getGaz"])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Groups(["getGaz"])]
    private ?string $gazName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["getGaz"])]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(["getGaz"])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'gazs')]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGazName(): ?string
    {
        return $this->gazName;
    }

    public function setGazName(string $gazName): self
    {
        $this->gazName = $gazName;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
