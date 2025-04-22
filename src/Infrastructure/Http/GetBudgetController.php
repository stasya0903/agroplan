<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\GetBudget\GetBudgetRequest;
use App\Application\UseCase\GetBudget\GetBudgetUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/budget/get',
    name: 'budget_get',
    methods: ['POST']
)]
final class GetBudgetController extends AbstractController
{
    public function __construct(
        private readonly GetBudgetUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] GetBudgetRequest $request): Response
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
