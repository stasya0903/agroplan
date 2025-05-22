<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SystemWorkType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Volume;

class Recipe
{
    private ?int $id = null;
    private ?Work $work = null;

    public function __construct(
        private Chemical $chemical,
        private Volume $dosis,
        private ?ProblemType $problem,
        private ?string $note
    ) {
    }

    public function getChemical(): Chemical
    {
        return $this->chemical;
    }

    public function setChemical(Chemical $chemical): void
    {
        $this->chemical = $chemical;
    }

    public function getDosis(): Volume
    {
        return $this->dosis;
    }

    public function setDosis(Volume $dosis): void
    {
        $this->dosis = $dosis;
    }

    public function getProblem(): ?ProblemType
    {
        return $this->problem;
    }

    public function setProblem(?ProblemType $problem): void
    {
        $this->problem = $problem;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function assignWork(Work $work): void
    {
        $this->work = $work;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function equals(Recipe $other): bool
    {
        return $this->getChemical()?->getId() === $other->getChemical()?->getId()
            && $this->getProblem() === $other->getProblem()
            && $this->getDosis()?->equals($other->getDosis())
            && $this->getNote() === $other->getNote();
    }
}
