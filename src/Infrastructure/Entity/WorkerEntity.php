<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'workers')]
class WorkerEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $dailyRateInCents;

    public function getDailyRateInCents(): int
    {
        return $this->dailyRateInCents;
    }

    public function setDailyRateInCents(int $dailyRateInCents): void
    {
        $this->dailyRateInCents = $dailyRateInCents;
    }

    public function __construct(string $name, int $dailyRateInCents)
    {
        $this->name = $name;
        $this->dailyRateInCents = $dailyRateInCents;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
