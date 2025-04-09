<?php

namespace App\Application\UseCase\getReport;

class GetReportRequest
{
    /**
     * @param iterable $ids
     */
    public function __construct(
        public readonly iterable $ids
    ) {
    }
}
