<?php

namespace App\Infrastructure\Entity;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\Worker;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'worker_shift')]
class WorkerShiftEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WorkerEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private WorkerEntity $worker;

    #[ORM\ManyToOne(inversedBy: 'workerShifts')]
    private WorkEntity $work;

    #[ORM\ManyToOne(targetEntity: PlantationEntity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private PlantationEntity $plantation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date;

    #[ORM\Column(type: 'integer')]
    private int $paymentInCents;

    #[ORM\Column(type: 'boolean', nullable:false)]
    private bool $paid;
    public function getWork(): WorkEntity
    {
        return $this->work;
    }

    public function setWork(WorkEntity $work): void
    {
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

    public function getWorker(): WorkerEntity
    {
        return $this->worker;
    }

    public function setWorker(WorkerEntity $worker): void
    {
        $this->worker = $worker;
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

    public function getPaymentInCents(): int
    {
        return $this->paymentInCents;
    }

    public function setPaymentInCents(int $paymentInCents): void
    {
        $this->paymentInCents = $paymentInCents;
    }

    public function isPaid(): bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
    }

    public function __construct(
        WorkerEntity $worker,
        PlantationEntity $plantation,
        WorkEntity $work,
        \DateTimeImmutable $date,
        int $payment,
        bool $paid = false
    ) {
        $this->worker = $worker;
        $this->plantation = $plantation;
        $this->work = $work;
        $this->date = $date;
        $this->paymentInCents = $payment;
        $this->paid = $paid;
    }
}
