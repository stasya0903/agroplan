<?php

declare(strict_types=1);

namespace App\Application\Gateway;

use App\Domain\ValueObject\Title;

class UrlGatewayResponse
{
    /**
     * @param string $title
     */
    public function __construct(
        public readonly string $title,
    ) {
    }
}
