<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\GetList\SpendingType\SpendingTypeListUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/spending_type/list',
    name: 'spending_type_list',
    methods: ['GET']
)]
final class GetSpendingTypeListController extends AbstractController
{
    public function __construct(
        private readonly SpendingTypeListUseCase $useCase
    ) {
    }

    public function __invoke(): Response
    {
        try {
            $response = ($this->useCase)();
            return $this->json($response);
        } catch (\Throwable $e) {
            $errorResponse = [
                'message' => $e->getMessage()
            ];
            return $this->json($errorResponse, 400);
        }
    }
}
