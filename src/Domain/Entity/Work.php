<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SystemWorkType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Note;

class Work
{
    private ?int $id = null;
    private array $workerShifts = [];

    private ?Spending $spending = null;

    public function __construct(
        private WorkType $workType,
        private Plantation $plantation,
        private Date $date,
        private array $workers,
        private Note $note

    ) {
        $this->validate();
    }

    public function getWorkerShifts(): array
    {
        return $this->workerShifts;
    }

    public function getSpending(): ?Spending
    {
        return $this->spending;
    }

    public function addWorkerShift(WorkerShift $shift): void
    {
        $this->workerShifts[] = $shift;
        $shift->assignToWork($this);
    }

    public function assignSpending(Spending $spending): void
    {
        $this->spending = $spending;
        $spending->assignToWork($this);
    }

    public function getWorkers(): array
    {
        return $this->workers;
    }

    public function getWorkType(): WorkType
    {
        return $this->workType;
    }

    public function getPlantation(): Plantation
    {
        return $this->plantation;
    }

    public function getNote(): Note
    {
        return $this->note;
    }
    public function getDate(): Date
    {
        return $this->date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function validate(): void
    {
        if ($this->workType->getId() === SystemWorkType::OTHER->value && !$this->note->getValue()) {
            throw new \DomainException('Note is required for OTHER work type.');
        }
    }
}
