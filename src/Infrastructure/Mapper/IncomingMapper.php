<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Incoming;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;
use App\Infrastructure\Entity\PlantationEntity;
use App\Infrastructure\Entity\WorkEntity;
use App\Infrastructure\Entity\WorkerEntity;
use App\Infrastructure\Entity\IncomingEntity;
use Doctrine\Common\Collections\ArrayCollection;

final class IncomingMapper
{
    public function __construct(
        private readonly PlantationMapper $plantationMapper,
        private readonly WorkMapper $workMapper,
    ) {
    }

    public function mapToDomain(IncomingEntity $entity): Incoming
    {
        $incoming = new Incoming(
            $this->plantationMapper->mapToDomain($entity->getPlantation()),
            new Date($entity->getDate()->format('Y-m-d H:i:s')),
            new Money($entity->getAmountInCents()),
            new Note($entity->getNote()),
            Weight::createFromGrams($entity->getWeightInGrams()),
            $entity->getType(),
            new Name($entity->getBuyerName()),
            new Money($entity->getPriceInCents()),
            $entity->getPaid()
        );
        if ($entity->getDatePaid()) {
            $incoming->setPaid(new Date($entity->getDatePaid()->format('Y-m-d H:i:s')));
        }
        $reflectionProperty = new \ReflectionProperty(Incoming::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($incoming, $entity->getId());

        return $incoming;
    }

    public function mapToEntity(
        Incoming $incoming,
        PlantationEntity $plantation,
        IncomingEntity $entity = null
    ): IncomingEntity {
        $date = $incoming->getDate()->getValue();
        $amount = $incoming->getAmount()->getAmount();
        $note = $incoming->getInfo()->getValue();
        $weight = $incoming->getWeight()->getGrams();
        $buyerName = $incoming->getBuyerName()->getValue();
        $price = $incoming->getPrice()->getAmount();
        $paid = $incoming->isPaid();
        $datePaid = $incoming->getDatePaid()?->getValue();
        if ($entity) {
            $entity->setPlantation($plantation);
            $entity->setDate($date);
            $entity->setAmountInCents($amount);
            $entity->setNote($note);
            $entity->setWeightInGrams($weight);
            $entity->setBuyerName($buyerName);
            $entity->setPriceInCents($price);
            $entity->setPaid($paid);
            $entity->setDatePaid($datePaid);
            $entity->setType($incoming->getIncomingTerm());
        } else {
            $entity = new IncomingEntity(
                $plantation,
                $date,
                $amount,
                $note,
                $weight,
                $incoming->getIncomingTerm(),
                $buyerName,
                $price,
                $paid,
                $datePaid
            );
        }
        return $entity;
    }
}
