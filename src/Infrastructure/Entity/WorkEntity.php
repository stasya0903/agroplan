<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\Spending;
use App\Domain\Entity\WorkerShift;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'work')]
class WorkEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $note;

    #[ORM\ManyToOne(targetEntity: WorkTypeEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private WorkTypeEntity $workType;

    #[ORM\ManyToOne(targetEntity: PlantationEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PlantationEntity $plantation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date;

    #[ORM\ManyToMany(targetEntity: WorkerEntity::class)]
    #[ORM\JoinTable(name: 'work_worker')]
    private Collection $workers;

    #[ORM\OneToOne(targetEntity: SpendingEntity::class, mappedBy: 'work', cascade: ['persist'])]
    private ?SpendingEntity $spending = null;

    #[ORM\OneToMany(targetEntity: WorkerShiftEntity::class, mappedBy: 'work', cascade: ['persist'], orphanRemoval: true)]
    private Collection $workerShifts;


    public function __construct(
        WorkTypeEntity $workType,
        PlantationEntity $plantation,
        \DateTimeImmutable $date,
        Collection $workers,
        string $note
    ) {
        $this->workType = $workType;
        $this->plantation = $plantation;
        $this->date = $date;
        $this->workers = $workers;
        $this->note = $note;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }

    public function getWorkType(): WorkTypeEntity
    {
        return $this->workType;
    }

    public function setWorkType(WorkTypeEntity $workType): void
    {
        $this->workType = $workType;
    }

    public function getPlantation(): PlantationEntity
    {
        return $this->plantation;
    }

    public function setPlantation(PlantationEntity $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getWorkers(): Collection
    {
        return $this->workers;
    }

    public function setWorkers(Collection $workers): void
    {
        $this->workers = $workers;
    }
}
