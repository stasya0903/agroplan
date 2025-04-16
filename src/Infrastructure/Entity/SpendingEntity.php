<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\Work;
use App\Domain\Enums\SpendingType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'spending')]
class SpendingEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PlantationEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PlantationEntity $plantation;

    #[ORM\Column(type: 'integer', enumType: SpendingType::class)]
    private SpendingType $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date;

    #[ORM\Column(type: 'integer')]
    private int $amountInCents;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $note;

    #[ORM\OneToOne(inversedBy: 'spending')]
    private ?WorkEntity $work = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getPlantation(): PlantationEntity
    {
        return $this->plantation;
    }

    public function setPlantation(PlantationEntity $plantation): void
    {
        $this->plantation = $plantation;
    }

    public function getType(): SpendingType
    {
        return $this->type;
    }

    public function setType(SpendingType $type): void
    {
        $this->type = $type;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function setAmountInCents(int $amountInCents): void
    {
        $this->amountInCents = $amountInCents;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): void
    {
        $this->note = $note;
    }
    public function getWork(): ?WorkEntity
    {
        return $this->work;
    }

    public function setWork(?WorkEntity $work): void
    {
        $this->work = $work;
    }


    public function __construct(
        PlantationEntity $plantation,
        SpendingType $type,
        \DateTimeImmutable $date,
        int $payment,
        ?string $note
    ) {
        $this->plantation = $plantation;
        $this->type = $type;
        $this->date = $date;
        $this->amountInCents = $payment;
        $this->note = $note;
    }




}
