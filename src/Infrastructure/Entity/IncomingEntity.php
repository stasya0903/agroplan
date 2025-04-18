<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\Work;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\SpendingType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'incoming')]
class IncomingEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PlantationEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PlantationEntity $plantation;

    #[ORM\Column(type: 'integer', enumType: IncomingTermType::class)]
    private IncomingTermType $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date;

    #[ORM\Column(type: 'integer')]
    private int $amountInCents;
    #[ORM\Column(type: 'integer')]
    private int $weightInGrams;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $note;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $buyerName;

    #[ORM\Column(type: 'integer')]
    private int $priceInCents;

    #[ORM\Column(type: 'boolean')]
    private bool $paid;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $datePaid;

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

    public function getType(): IncomingTermType
    {
        return $this->type;
    }

    public function setType(IncomingTermType $type): void
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

    public function getWeightInGrams(): int
    {
        return $this->weightInGrams;
    }

    public function setWeightInGrams(int $weightInGrams): void
    {
        $this->weightInGrams = $weightInGrams;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getBuyerName(): ?string
    {
        return $this->buyerName;
    }

    public function setBuyerName(?string $buyerName): void
    {
        $this->buyerName = $buyerName;
    }

    public function getPriceInCents(): int
    {
        return $this->priceInCents;
    }

    public function setPriceInCents(int $priceInCents): void
    {
        $this->priceInCents = $priceInCents;
    }

    public function getPaid(): int
    {
        return $this->paid;
    }

    public function setPaid(int $paid): void
    {
        $this->paid = $paid;
    }

    public function getDatePaid(): ?\DateTimeImmutable
    {
        return $this->datePaid;
    }

    public function setDatePaid(?\DateTimeImmutable $datePaid): void
    {
        $this->datePaid = $datePaid;
    }


    public function __construct(
        PlantationEntity $plantation,
        \DateTimeImmutable $date,
        int $amountInCents,
        ?string $note,
        int $weightInGrams,
        IncomingTermType $type,
        string $buyerName,
        int $priceInCents,
        bool $paid,
        ?\DateTimeImmutable $datePaid
    ) {
        $this->plantation = $plantation;
        $this->date = $date;
        $this->amountInCents = $amountInCents;
        $this->note = $note;
        $this->weightInGrams = $weightInGrams;
        $this->type = $type;
        $this->buyerName = $buyerName;
        $this->priceInCents = $priceInCents;
        $this->paid = $paid;
        $this->datePaid = $datePaid;
    }
}
