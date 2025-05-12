<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\DeleteChemical\DeleteChemicalRequest;
use App\Application\UseCase\DeleteChemical\DeleteChemicalUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/chemical/delete',
    name: 'chemical_delete',
    methods: ['DELETE']
)]
final class DeleteChemicalController extends AbstractController
{
    public function __construct(
        private readonly DeleteChemicalUseCase $useCase,
    ) {
    }

    public function __invoke(#[MapRequestPayload] DeleteChemicalRequest $request): Response
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
