<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateChemical\CreateChemicalRequest;
use App\Application\UseCase\CreateChemical\CreateChemicalUseCase;
use App\Application\UseCase\CreatePlantation\CreatePlantationRequest;
use App\Application\UseCase\CreatePlantation\CreatePlantationUseCase;
use App\Application\UseCase\EditChemical\DeleteChemicalRequest;
use App\Application\UseCase\EditChemical\DeleteChemicalUseCase;
use App\Application\UseCase\EditChemical\EditChemicalRequest;
use App\Application\UseCase\EditChemical\EditChemicalUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/chemical/edit',
    name: 'chemical_edit',
    methods: ['POST']
)]
final class EditChemicalController extends AbstractController
{
    public function __construct(
        private readonly EditChemicalUseCase $useCase,
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditChemicalRequest $request): Response
    {
        try {
            $response = ($this->useCase)($request);
            return $this->json($response);
        } catch (\Throwable $e) {
            $errorResponse = [
                'message' => $e->getMessage()
            ];
            return $this->json($errorResponse, 400);
        }
    }
}
