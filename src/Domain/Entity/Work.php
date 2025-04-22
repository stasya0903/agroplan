<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SystemWorkType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
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

    public function setWorkType(WorkType $workType): void
    {
        $this->workType = $workType;
    }

    public function setPlantation(Plantation $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function setDate(Date $date): void
    {
        $this->date = $date;
    }

    public function setWorkers(array $workers): void
    {
        $this->workers = $workers;
    }

    public function setNote(Note $note): void
    {
        $this->note = $note;
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

    public function removeWorkerShift(WorkerShift $shift): void
    {
        foreach ($this->workerShifts as $index => $existingShift) {
            if ($existingShift === $shift) {
                unset($this->workerShifts[$index]);
                $this->workerShifts = array_values($this->workerShifts);
                break;
            }
        }
    }

    public function getFullPrice(): int
    {
        $total = 0;
        foreach ($this->workers as $worker) {
            $total += $worker->getDailyRate()->getAmount();
        }
        return $total;
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
