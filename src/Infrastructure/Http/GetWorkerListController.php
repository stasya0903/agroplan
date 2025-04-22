<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateWorker\CreateWorkerRequest;
use App\Application\UseCase\CreateWorker\CreateWorkerUseCase;
use App\Application\UseCase\EditWorker\EditWorkerRequest;
use App\Application\UseCase\EditWorker\EditWorkerUseCase;
use App\Application\UseCase\GetList\Worker\GetWorkerListRequest;
use App\Application\UseCase\GetList\Worker\GetWorkerListUseCase;
use App\Infrastructure\Http\Request\HttpCreateWorkerRequest;
use App\Infrastructure\Http\Request\HttpGetWorkerListRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/v1/worker/list',
    name: 'worker_list',
    methods: ['POST']
)]
final class GetWorkerListController extends AbstractController
{
    public function __construct(
        private readonly GetWorkerListUseCase $useCase
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] HttpGetWorkerListRequest $httpRequest,
        ValidatorInterface $validator
    ): Response {
        try {
            $errors = $validator->validate($httpRequest);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], 400);
            }
            $request = $httpRequest->toApplicationRequest();
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
