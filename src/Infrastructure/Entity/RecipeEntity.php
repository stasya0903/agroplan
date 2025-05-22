<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'recipes')]
class RecipeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ChemicalEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ChemicalEntity $chemical;
    #[ORM\ManyToOne(targetEntity: ProblemTypeEntity::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProblemTypeEntity $problem;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    private WorkEntity $work;

    #[ORM\Column(type: 'integer')]
    private int $dosisInMl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $note;

    public function __construct(
        ChemicalEntity $chemical,
        float $dosisInMl,
        WorkEntity $work,
        ?ProblemTypeEntity $problem = null,
        ?string $note = null,

    )
    {
        $this->chemical = $chemical;
        $this->problem = $problem;
        $this->dosisInMl = $dosisInMl;
        $this->note = $note;
        $this->work = $work;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getChemical(): ChemicalEntity
    {
        return $this->chemical;
    }

    public function setChemical(ChemicalEntity $chemical): void
    {
        $this->chemical = $chemical;
    }

    public function getProblem(): ProblemTypeEntity
    {
        return $this->problem;
    }

    public function setProblem(?ProblemTypeEntity $problem): void
    {
        $this->problem = $problem;
    }

    public function getDosisInMl(): int
    {
        return $this->dosisInMl;
    }

    public function setDosisInMl(int $dosisInMl): void
    {
        $this->dosisInMl = $dosisInMl;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getWork(): WorkEntity
    {
        return $this->work;
    }

    public function setWork(WorkEntity $work): void
    {
        $this->work = $work;
    }
}
