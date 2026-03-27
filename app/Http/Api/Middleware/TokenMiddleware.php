<?php

declare(strict_types=1);

namespace App\Http\Api\Middleware;

use Mine\Jwt\JwtInterface;
use Mine\JwtAuth\Middleware\AbstractTokenMiddleware;

final class TokenMiddleware extends AbstractTokenMiddleware
{
    public function getJwt(): JwtInterface
    {
        return $this->jwtFactory->get('api');
    }
}
