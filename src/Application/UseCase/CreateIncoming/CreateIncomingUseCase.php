<?php

namespace App\Application\UseCase\CreateIncoming;

use App\Domain\Enums\IncomingTermType;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;

class CreateIncomingUseCase
{
    public function __construct(
        private readonly IncomingFactoryInterface $factory,
        private readonly IncomingRepositoryInterface $repository,
        private readonly PlantationRepositoryInterface $plantationRepository,
    ) {
    }

    public function __invoke(CreateIncomingRequest $request): CreateIncomingResponse
    {
        $incomingType = IncomingTermType::tryFrom($request->incomingTermId);
        if (!$incomingType) {
            throw new \DomainException('Incoming term type not found.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }
        $weight = new Weight($request->weight);
        $price = Money::fromFloat($request->price);
        $amount = $weight->getKg() * $price->getAmountAsFloat();
        $incoming = $this->factory->create(
            $plantation,
            new Date($request->date),
            Money::fromFloat($amount),
            new Note($request->note),
            $weight,
            $incomingType,
            new Name($request->buyerName),
            $price
        );
        $this->repository->save($incoming);
        return new CreateIncomingResponse($incoming->getId());
    }
}
