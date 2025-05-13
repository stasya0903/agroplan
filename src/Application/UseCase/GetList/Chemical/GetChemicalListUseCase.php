<?php

namespace App\Application\UseCase\GetList\Chemical;

use App\Application\DTO\ChemicalDTO;
use App\Application\UseCase\GetList\Chemical\GetChemicalListRequest;
use App\Application\UseCase\GetList\Chemical\GetChemicalListResponse;
use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Infrastructure\Repository\ChemicalRepository;

class GetChemicalListUseCase
{
    public function __construct(
        private readonly ChemicalRepositoryInterface $chemicalRepository
    ) {
    }

    public function __invoke(): GetChemicalListResponse
    {
        $list = $this->chemicalRepository->getList([]);
        $chemicals = array_map(fn(Chemical $chemical) => new ChemicalDTO(
            $chemical->getId(),
            $chemical->getCommercialName()->getValue(),
            $chemical->getActiveIngredient()?->getValue()
        ), $list);
        return new GetChemicalListResponse($chemicals);
    }
}
