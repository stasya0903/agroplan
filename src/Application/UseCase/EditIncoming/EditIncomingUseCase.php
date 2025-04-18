<?php

namespace App\Application\UseCase\EditIncoming;

use App\Application\DTO\IncomingDTO;
use App\Application\UseCase\EditIncoming\EditIncomingRequest;
use App\Application\UseCase\EditIncoming\EditIncomingResponse;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\IncomingType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;
use App\Infrastructure\Repository\IncomingRepository;
use Doctrine\ORM\Exception\ORMException;

class EditIncomingUseCase
{
    public function __construct(
        private readonly IncomingRepository $incomingRepository,
        private readonly PlantationRepositoryInterface $plantationRepository
    ) {
    }

    /**
     * @throws ORMException
     */
    public function __invoke(EditIncomingRequest $request): EditIncomingResponse
    {
        $incoming = $this->incomingRepository->find($request->incomingId);
        if (!$incoming) {
            throw new \DomainException('Incoming not found.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }
        $incomingType = IncomingTermType::tryFrom($request->incomingTermId);
        if (!$incomingType) {
            throw new \DomainException('Incoming term type not found.');
        }
        if ($request->paid && !$request->datePaid) {
            throw new \DomainException('To pay incoming please add paid date.');
        }

        $incoming->setPlantation($plantation);
        $incoming->setDate(new Date($request->date));
        $incoming->setInfo(new Note($request->note));
        $incoming->setWeight($weight = new Weight($request->weight));
        $incoming->setPrice($price = Money::fromFloat($request->price));
        $incoming->setIncomingTerm($incomingType);
        $incoming->setBuyerName(new Name($request->buyerName));
        if ($request->paid) {
            $incoming->setPaid(new Date($request->datePaid));
        } else {
            $incoming->setUnpaid();
        }
        $amount = $weight->getKg() * $price->getAmountAsFloat();
        $incoming->setAmount(Money::fromFloat($amount));
        $this->incomingRepository->save($incoming);
        return new EditIncomingResponse(
            new IncomingDTO(
                $incoming->getId(),
                $incoming->getDate()->getValue()->format('Y-m-d'),
                $incoming->getPlantation()->getId(),
                $incoming->getPlantation()->getName()->getValue(),
                $incoming->getAmount()->getAmountAsFloat(),
                $incoming->getInfo()->getValue(),
                $incoming->getWeight()->getKg(),
                $incoming->getIncomingTerm()->value,
                $incoming->getIncomingTerm()->label(),
                $incoming->getBuyerName()->getValue(),
                $incoming->getPrice()->getAmountAsFloat(),
                $incoming->isPaid(),
                $incoming->getDatePaid()?->getValue()->format('Y-m-d')
            )
        );
    }
}
