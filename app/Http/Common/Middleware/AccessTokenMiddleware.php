<?php

declare(strict_types=1);

namespace App\Http\Common\Middleware;

use Mine\Jwt\JwtInterface;
use Mine\JwtAuth\Middleware\AbstractTokenMiddleware;

final class AccessTokenMiddleware extends AbstractTokenMiddleware
{
    public function getJwt(): JwtInterface
    {
        return $this->jwtFactory->get();
    }
}
