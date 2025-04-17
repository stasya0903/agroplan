<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\SetPaidWorkerShifts\SetPaidWorkerShiftsRequest;
use App\Application\UseCase\SetPaidWorkerShifts\SetPaidWorkerShiftsUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/worker_shift/set_paid',
    name: 'worker_shift_set_paid',
    methods: ['POST']
)]
final class SetPaidWorkerShiftsController extends AbstractController
{
    public function __construct(
        private readonly SetPaidWorkerShiftsUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] SetPaidWorkerShiftsRequest $request): Response
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
