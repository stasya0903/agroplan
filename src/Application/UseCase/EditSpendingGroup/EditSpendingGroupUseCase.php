<?php

namespace App\Application\UseCase\EditSpendingGroup;

use App\Application\DTO\SpendingDTO;
use App\Application\DTO\SpendingGroupDTO;
use App\Domain\Enums\SpendingType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Repository\SpendingGroupRepository;
use App\Infrastructure\Repository\SpendingRepository;
use Doctrine\ORM\Exception\ORMException;

class EditSpendingGroupUseCase
{
    public function __construct(
        private readonly SpendingRepository $spendingRepository,
        private readonly SpendingGroupRepository $spendingGroupRepository,

    ) {
    }

    /**
     * @throws ORMException
     */
    public function __invoke(EditSpendingGroupRequest $request): EditSpendingGroupResponse
    {
        $spendingGroup = $this->spendingGroupRepository->find($request->spendingGroupId);
        if (!$spendingGroup) {
            throw new \DomainException('Spending group not found.');
        }
        $spendingType = SpendingType::tryFrom($request->spendingTypeId);
        if (!$spendingType) {
            throw new \DomainException('Spending type not found.');
        }
        if ($spendingGroup->getType() === SpendingType::WORK) {
            throw new \DomainException('Please edit work for Work spending type.');
        }
        if ($spendingType === SpendingType::WORK) {
            throw new \DomainException('Please create work for Work spending type.');
        }
        $oldAmount = $spendingGroup->getAmount()->getAmount();
        $newAmount = Money::fromFloat($request->amount);
        $allSpendings = $this->spendingRepository->getForGroup($spendingGroup->getId());
        if($oldAmount !== $newAmount->getAmount()) {
            $spendingGroup->setAmount($newAmount);
            $totalAmountInCents = $newAmount->getAmount();
            $plantationCount = count($allSpendings);
            $baseAmount = intdiv($totalAmountInCents, $plantationCount);
            $remainder = $totalAmountInCents % $plantationCount;

            foreach ($allSpendings as $index => $spending) {
                $amount = $baseAmount + ($index < $remainder ? 1 : 0);
                $spending->setAmount(new Money($amount));
                $this->spendingRepository->save($spending);
            }
        }

        $spendingGroup->setType($spendingType);
        $spendingGroup->setDate(new Date($request->date));
        $spendingGroup->setInfo(new Note($request->note));
        $this->spendingGroupRepository->save($spendingGroup);
        $spendingsDto = [];
         foreach($allSpendings as $spending) {
             $spendingsDto[] = new SpendingDTO(
                 $spending->getId(),
                 $spending->getPlantation()->getId(),
                 $spending->getPlantation()->getName()->getValue(),
                 $spending->getAmount()->getAmountAsFloat(),
             );
         }

        return new EditSpendingGroupResponse(
            new SpendingGroupDTO(
                $spendingGroup->getId(),
                $spendingGroup->getDate()->getValue()->format('Y-m-d'),
                $spendingGroup->getType()->value,
                $spendingGroup->getType()->label(),
                $spendingGroup->getAmount()->getAmountAsFloat(),
                $spendingGroup->getInfo()->getValue(),
                $spendingsDto
            )
        );
    }
}
