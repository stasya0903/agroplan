<?php

declare(strict_types=1);

namespace App\Application\Gateway;

interface UrlGatewayInterface
{
    /**
     * @param UrlGatewayRequest $request
     * @return UrlGatewayResponse
     */
    public function visitLink(UrlGatewayRequest $request): UrlGatewayResponse;
}
