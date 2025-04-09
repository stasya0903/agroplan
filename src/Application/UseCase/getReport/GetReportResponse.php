<?php

namespace App\Application\UseCase\getReport;

class GetReportResponse
{
    public function __construct(
        public string $fileUrl
    ) {
    }
}
