<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SystemWorkType;
use App\Domain\ValueObject\Note;

class Work
{
    private ?int $id = null;

    public function __construct(
        private int $workTypeId,
        private int $plantationId,
        private \DateTimeInterface $date,
        private array $workerIds,
        private Note $note

    ) {
        $this->validate();
    }

    public function getPlantationId(): int
    {
        return $this->plantationId;
    }

    public function getNote(): Note
    {
        return $this->note;
    }

    public function getWorkerIds(): array
    {
        return $this->workerIds;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getWorkTypeId(): int
    {
        return $this->workTypeId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function validate(): void
    {
        if ($this->workTypeId === SystemWorkType::OTHER->value && !$this->note) {
            throw new \DomainException('Note is required for OTHER work type.');
        }
    }

}