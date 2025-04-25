<?php

namespace App\Application\UseCase\EditSpending;

use App\Application\DTO\SpendingDTO;
use App\Application\Shared\TransactionalSessionInterface;
use App\Application\UseCase\EditSpendingGroup\EditSpendingGroupResponse;
use App\Domain\Enums\SpendingType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Repository\SpendingRepository;
use Doctrine\ORM\Exception\ORMException;

class EditSpendingUseCase
{
    public function __construct(
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly PlantationRepositoryInterface $plantationRepository,
        private readonly TransactionalSessionInterface $transaction
    ) {
    }

    /**
     * @throws ORMException
     */
    public function __invoke(EditSpendingRequest $request): EditSpendingResponse
    {
        $spending = $this->spendingRepository->find($request->spendingId);
        if (!$spending) {
            throw new \DomainException('Spending not found.');
        }

        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }
        $spendingGroup = $spending->getSpendingGroup();

        if ($spendingGroup->getType() === SpendingType::WORK) {
            throw new \DomainException('Please edit work for Work spending type.');
        }
        $groupTotal = $spendingGroup->getAmount()->getAmount();
        $newAmount = Money::fromFloat($request->amount);


        $allOtherSpending = $this->spendingRepository->getForGroup($spendingGroup->getId(), [$spending->getId()]);

        if(!count($allOtherSpending) && $newAmount->getAmount() !== $groupTotal) {
            throw new \DomainException('Please change group amount for only spending in group.');
        }
        if(count($allOtherSpending) && $newAmount->getAmount() === $groupTotal) {
            throw new \DomainException('You cannot assign all amount to one spending.');
        }
        if (count($allOtherSpending) && $groupTotal < $newAmount->getAmount()) {
            throw new \DomainException('Amount should be less than group amount.');
        }

        return $this->transaction->run(function () use ($allOtherSpending, $spendingGroup, $groupTotal, $request, $newAmount, $plantation, $spending) {
            $spending->setPlantation($plantation);
            if ($spending->getAmount()->getAmount() !== $newAmount->getAmount()) {
                $spending->setAmount($newAmount);
                if(count($allOtherSpending)){
                    $remainderInCents = $groupTotal - $newAmount->getAmount();
                    $baseAmount = intdiv($remainderInCents, count($allOtherSpending));
                    $remainder = $remainderInCents % count($allOtherSpending);

                    foreach ($allOtherSpending as $index => $remindSpending) {
                        $amount = $baseAmount + ($index < $remainder ? 1 : 0);
                        $remindSpending->setAmount(new Money($amount));
                        $this->spendingRepository->save($remindSpending);
                    }
                }
            }

            $this->spendingRepository->save($spending);
            return new EditSpendingResponse(
                new SpendingDTO(
                    $spending->getId(),
                    $spending->getPlantation()->getId(),
                    $spending->getPlantation()->getName()->getValue(),
                    $spending->getAmount()->getAmountAsFloat()
                )
            );
        });
    }
}
