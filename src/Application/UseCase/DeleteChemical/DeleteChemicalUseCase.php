<?php

namespace App\Application\UseCase\DeleteChemical;

use App\Domain\Repository\ChemicalRepositoryInterface;

class DeleteChemicalUseCase
{
    public function __construct(
        private readonly ChemicalRepositoryInterface $chemicalRepository
    ) {
    }

    public function __invoke(DeleteChemicalRequest $request): DeleteChemicalResponse
    {
        $chemical = $this->chemicalRepository->find($request->id);

        if (!$chemical) {
            throw new \DomainException('Chemical not found.');
        }
        $this->chemicalRepository->delete($chemical->getId());

        return new DeleteChemicalResponse(true);
    }
}
