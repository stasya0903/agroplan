<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreatePlantation\CreatePlantationRequest;
use App\Application\UseCase\CreatePlantation\CreatePlantationUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/plantation/add',
    name: 'plantation_add',
    methods: ['POST']
)]
final class CreatePlantationController extends AbstractController
{
    public function __construct(
        private readonly CreatePlantationUseCase $useCase,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreatePlantationRequest $request): Response
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