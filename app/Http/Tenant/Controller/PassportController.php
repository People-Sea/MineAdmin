<?php

declare(strict_types=1);

namespace App\Http\Tenant\Controller;

use App\Http\Common\Controller\AbstractController;
use App\Http\Admin\Vo\PassportLoginVo;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\RefreshTokenMiddleware;
use App\Model\Enums\User\Type;
use App\Http\Tenant\Request\PassportLoginRequest;
use App\Service\PassportService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation as OA;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\HttpServer\Contract\RequestInterface;
use Mine\Swagger\Attributes\ResultResponse;

#[OA\HyperfServer(name: 'http')]
final class PassportController extends AbstractController
{
    public function __construct(
        private readonly PassportService $passportService,
        private readonly CurrentUser $currentUser
    ) {}

    #[Post(
        path: '/tenant/passport/login',
        operationId: 'tenantPassportLogin',
        summary: '租户登录',
        tags: ['tenant:passport']
    )]
    #[ResultResponse(instance: new Result(data: new PassportLoginVo()))]
    #[OA\RequestBody(content: new OA\JsonContent(
        ref: PassportLoginRequest::class,
        title: '租户登录请求参数',
        required: ['username', 'password'],
        example: '{"username":"tenant_demo","password":"123456"}'
    ))]
    public function login(PassportLoginRequest $request): Result
    {
        $browser = $request->header('User-Agent') ?: 'unknown';
        $os = $request->os();
        return $this->success(
            $this->passportService->login(
                (string) $request->input('username'),
                (string) $request->input('password')
                ,
                Type::USER,
                $request->ip(),
                $browser,
                $os
            )
        );
    }

    #[Post(
        path: '/tenant/passport/logout',
        operationId: 'tenantPassportLogout',
        summary: '租户退出',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['tenant:passport']
    )]
    #[ResultResponse(instance: new Result())]
    #[Middleware(AccessTokenMiddleware::class)]
    public function logout(RequestInterface $request): Result
    {
        $this->passportService->logout($this->currentUser->getToken());
        return $this->success();
    }

    #[Post(
        path: '/tenant/passport/refresh',
        operationId: 'tenantPassportRefresh',
        summary: '刷新租户 token',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['tenant:passport']
    )]
    #[Middleware(RefreshTokenMiddleware::class)]
    #[ResultResponse(instance: new Result(data: new PassportLoginVo()))]
    public function refresh(): Result
    {
        return $this->success($this->currentUser->refresh());
    }
}
