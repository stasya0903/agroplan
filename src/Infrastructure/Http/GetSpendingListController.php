<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\GetList\Spending\GetSpendingListRequest;
use App\Application\UseCase\GetList\Spending\GetSpendingListUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/spending/list',
    name: 'spending_list',
    methods: ['POST']
)]
final class GetSpendingListController extends AbstractController
{
    public function __construct(
        private readonly GetSpendingListUseCase $useCase
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] GetSpendingListRequest $request
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
