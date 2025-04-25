<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\Spending;
use App\Domain\Entity\Work;
use App\Domain\Enums\SpendingType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'spending_group')]
class SpendingGroupEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
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

    #[ORM\OneToMany(
        targetEntity: SpendingEntity::class,
        mappedBy: 'spendingGroup',
        cascade: ['persist', 'remove'],
        orphanRemoval: true)]
    private ?Collection $spendings;
    #[ORM\Column(type: 'boolean')]
    private ?bool $isShared;

    public function getIsShared(): ?bool
    {
        return $this->isShared;
    }

    public function setIsShared(?bool $isShared): void
    {
        $this->isShared = $isShared;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
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

    public function getSpendings(): Collection
    {
        return $this->spendings;
    }

    public function setSpendings(Collection $spendings): void
    {
        $this->spendings = $spendings;
    }

    public function __construct(
        SpendingType $type,
        \DateTimeImmutable $date,
        int $payment,
        ?string $note,
        ?bool $isShared = false,
        ?Collection $spendings = new ArrayCollection()
    ) {
        $this->type = $type;
        $this->date = $date;
        $this->amountInCents = $payment;
        $this->note = $note;
        $this->isShared = $isShared;
        $this->spendings = $spendings;
    }


}
