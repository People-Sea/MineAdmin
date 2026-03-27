<?php

declare(strict_types=1);

namespace App\Http\Common\Middleware;

use Lcobucci\JWT\UnencryptedToken;
use Mine\Jwt\JwtInterface;
use Mine\JwtAuth\Middleware\AbstractTokenMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

class RefreshTokenMiddleware extends AbstractTokenMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->checkToken->checkJwt($this->parserToken($request));
        return $handler->handle(
            value(
                static function (ServerRequestPlusInterface $request, UnencryptedToken $token) {
                    return $request->setAttribute('token', $token);
                },
                $request,
                $this->getJwt()->parserRefreshToken(
                    $this->getToken($request)
                )
            )
        );
    }

    public function getJwt(): JwtInterface
    {
        return $this->jwtFactory->get();
    }

    protected function parserToken(ServerRequestInterface $request): UnencryptedToken
    {
        return $this->getJwt()->parserRefreshToken($this->getToken($request));
    }
}
