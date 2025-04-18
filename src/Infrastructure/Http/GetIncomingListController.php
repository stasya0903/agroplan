<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;


use App\Application\UseCase\GetList\Incoming\GetIncomingListRequest;
use App\Application\UseCase\GetList\Incoming\GetIncomingListUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/incoming/list',
    name: 'incoming_list',
    methods: ['POST']
)]
final class GetIncomingListController extends AbstractController
{
    public function __construct(
        private readonly GetIncomingListUseCase $useCase
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] GetIncomingListRequest $request
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
