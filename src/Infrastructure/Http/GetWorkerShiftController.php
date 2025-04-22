<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\GetList\WorkerShift\GetWorkerShiftListRequest;
use App\Application\UseCase\GetList\WorkerShift\GetWorkerShiftListUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/worker_shift/list',
    name: 'worker_shift_list',
    methods: ['POST']
)]
final class GetWorkerShiftController extends AbstractController
{
    public function __construct(
        private readonly GetWorkerShiftListUseCase $useCase
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] GetWorkerShiftListRequest $request
    ): Response {
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
