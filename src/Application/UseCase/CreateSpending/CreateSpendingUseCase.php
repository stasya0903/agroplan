<?php

namespace App\Application\UseCase\CreateSpending;

use App\Domain\Enums\SpendingType;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;

class CreateSpendingUseCase
{
    public function __construct(
        private readonly SpendingFactoryInterface $factory,
        private readonly SpendingRepositoryInterface $repository,
        private readonly PlantationRepositoryInterface $plantationRepository,
    ) {
    }

    public function __invoke(CreateSpendingRequest $request): CreateSpendingResponse
    {
        $spendingType = SpendingType::tryFrom($request->spendingTypeId);
        if (!$spendingType) {
            throw new \DomainException('Spending type not found.');
        }
        if ($spendingType === SpendingType::WORK) {
            throw new \DomainException('Please create work for Work spending type.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }
        $spending = $this->factory->create(
            $plantation,
            $spendingType,
            new Date($request->date),
            Money::fromFloat($request->amount),
            new Note($request->note)
        );
        $this->repository->save($spending);
        return new CreateSpendingResponse($spending->getId());
    }
}
