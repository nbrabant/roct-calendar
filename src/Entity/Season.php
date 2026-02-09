<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function __toString(): string
    {
        if ($this->startDate === null || $this->endDate === null) {
            return '';
        }

        return $this->startDate->format('Y') . '/' . $this->endDate->format('Y');
    }
}
