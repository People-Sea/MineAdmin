<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\V1;

use App\Http\Api\Request\V1\UserRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\Post;

#[HyperfServer(name: 'http')]
final class UserController extends AbstractController
{
    #[Post(
        path: '/api/v1/login',
        operationId: 'ApiV1Login',
        summary: '用户登录',
        tags: ['api'],
    )]
    public function login(UserRequest $request): Result
    {
        $validated = $request->validated();
        // your login logic here
        return $this->success();
    }
}
