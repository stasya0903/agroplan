<?php

namespace App\Application\UseCase\EditSpending;

use App\Application\DTO\SpendingDTO;
use App\Domain\Enums\SpendingType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Infrastructure\Repository\SpendingRepository;
use Doctrine\ORM\Exception\ORMException;

class EditSpendingUseCase
{
    public function __construct(
        private readonly SpendingRepository $spendingRepository,
        private readonly PlantationRepositoryInterface $plantationRepository
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
        $spendingType = SpendingType::tryFrom($request->spendingTypeId);
        if (!$spendingType) {
            throw new \DomainException('Spending type not found.');
        }
        if ($spendingType === SpendingType::WORK) {
            throw new \DomainException('Please edit work for Work spending type.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }

        $spending->setPlantation($plantation);
        $spending->setType($spendingType);
        $spending->setDate(new Date($request->date));
        $spending->setAmount(Money::fromFloat($request->amount));
        $spending->setInfo(new Note($request->note));
        $this->spendingRepository->save($spending);
        return new EditSpendingResponse(
            new SpendingDTO(
                $spending->getId(),
                $spending->getDate()->getValue()->format('Y-m-d'),
                $spending->getPlantation()->getId(),
                $spending->getPlantation()->getName()->getValue(),
                $spending->getType()->value,
                $spending->getType()->label(),
                $spending->getAmount()->getAmountAsFloat(),
                $spending->getInfo()->getValue()
            )
        );
    }
}
