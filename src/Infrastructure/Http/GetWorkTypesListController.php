<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateWorkType\CreateWorkTypeRequest;
use App\Application\UseCase\CreateWorkType\CreateWorkTypeUseCase;
use App\Application\UseCase\EditWorkType\EditWorkTypeRequest;
use App\Application\UseCase\EditWorkType\EditWorkTypeUseCase;
use App\Application\UseCase\GetList\WorkType\GetWorkTypeListRequest;
use App\Application\UseCase\GetList\WorkType\GetWorkTypeListUseCase;
use App\Infrastructure\Http\Request\HttpCreateWorkTypeRequest;
use App\Infrastructure\Http\Request\HttpGetWorkTypeListRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/v1/work_type/list',
    name: 'work_type_list',
    methods: ['POST']
)]
final class GetWorkTypesListController extends AbstractController
{
    public function __construct(
        private readonly GetWorkTypeListUseCase $useCase
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] HttpGetWorkTypeListRequest $httpRequest,
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
