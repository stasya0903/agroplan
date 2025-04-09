<?php

namespace App\Application\UseCase\getReport;

use App\Application\Generator\ReportGeneratorInterface;
use App\Application\Query\GetNewsHandler;
use App\Application\Query\GetNewsQuery;
use App\Domain\Repository\NewsRepositoryInterface;
use App\Domain\Repository\ReportRepositoryInterface;

class GetReportUseCase
{
    public function __construct(
        private readonly GetNewsHandler $getNewsHandler,
        private readonly ReportGeneratorInterface $reportGenerator
    ) {
    }

    public function __invoke(GetReportRequest $request): GetReportResponse
    {
        $query = new GetNewsQuery($request->ids);
        $news = $this->getNewsHandler->handle($query);
        $report = $this->reportGenerator->generate($news);
        return new GetReportResponse($report->getLink());
    }
}
