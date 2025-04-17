<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateSpending\CreateSpendingRequest;
use App\Application\UseCase\CreateSpending\CreateSpendingUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/spending/add',
    name: 'spending_add',
    methods: ['POST']
)]
final class CreateSpendingController extends AbstractController
{
    public function __construct(
        private readonly CreateSpendingUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateSpendingRequest $request): Response
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
