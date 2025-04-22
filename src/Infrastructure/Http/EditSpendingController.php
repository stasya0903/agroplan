<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\EditSpending\EditSpendingRequest;
use App\Application\UseCase\EditSpending\EditSpendingUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/spending/edit',
    name: 'spending_edit',
    methods: ['POST']
)]
final class EditSpendingController extends AbstractController
{
    public function __construct(
        private readonly EditSpendingUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditSpendingRequest $request): Response
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
