<?php

namespace App\Application\Generator;

use App\Domain\Entity\News;
use App\Domain\Entity\Report;

interface ReportGeneratorInterface
{
    /**
     * @param News[] $news
     * @return Report
     */
    public function generate(iterable $news): Report;
}
