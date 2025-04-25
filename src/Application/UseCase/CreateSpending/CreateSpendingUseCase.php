<?php

namespace App\Application\UseCase\CreateSpending;

use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Factory\SpendingGroupFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
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
        private readonly SpendingGroupFactoryInterface $groupFactory,
        private readonly SpendingGroupRepositoryInterface $groupRepository,
        private readonly TransactionalSessionInterface $transaction
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
        if (count($request->plantationIds) === 0) {
            throw new \DomainException('Please chose at list one plantation for spending.');
        }
        $plantations = [];
        foreach ($request->plantationIds as $plantationId) {
            $plantation = $this->plantationRepository->find($plantationId);
            if (!$plantation) {
                throw new \DomainException('Plantation not found.');
            }
            $plantations[] = $plantation;
        }
        return $this->transaction->run(function () use ($spendingType, $request, $plantations) {
            $plantationCount = count($plantations);
            $totalAmountInCents = Money::fromFloat($request->amount)->getAmount();
            $spendingGroup = $this->groupFactory->create(
                $spendingType,
                new Date($request->date),
                new Money($totalAmountInCents),
                new Note($request->note),
                $plantationCount > 0
            );
            $this->groupRepository->save($spendingGroup);

            $baseAmount = intdiv($totalAmountInCents, $plantationCount);
            $remainder = $totalAmountInCents % $plantationCount;

            $spendings = [];
            foreach ($plantations as $index => $plantation) {
                $amount = $baseAmount + ($index < $remainder ? 1 : 0);
                $spending = $this->factory->create(
                    $spendingGroup,
                    $plantation,
                    new Money($amount)
                );
                $this->repository->save($spending);
                $spendings[] = $spending->getId();
            }

            return new CreateSpendingResponse($spendingGroup->getId(), $spendings);
        });
    }
}
