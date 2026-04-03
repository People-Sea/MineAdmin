<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Platform;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Platform\PlatformAppRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\PlatformAppSchema;
use App\Service\Platform\PlatformAppService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Mine\Access\Attribute\Permission;
use Mine\Swagger\Attributes\PageResponse;
use Mine\Swagger\Attributes\ResultResponse;
use OpenApi\Attributes\RequestBody;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class PlatformAppController extends AbstractController
{
    public function __construct(
        private readonly PlatformAppService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/platform-app/list',
        operationId: 'platformAppList',
        summary: '平台应用列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[PageResponse(instance: PlatformAppSchema::class)]
    #[Permission(code: 'platform:platform-app:index')]
    public function pageList(): Result
    {
        return $this->success(
            $this->service->page(
                $this->getRequestData(),
                $this->getCurrentPage(),
                $this->getPageSize()
            )
        );
    }

    #[Get(
        path: '/admin/platform-app/options',
        operationId: 'platformAppOptions',
        summary: '平台应用选项列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[Permission(code: 'platform:platform-app:index')]
    #[ResultResponse(instance: new Result())]
    public function options(): Result
    {
        return $this->success($this->service->options());
    }

    #[Post(
        path: '/admin/platform-app',
        operationId: 'platformAppCreate',
        summary: '创建平台应用',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: PlatformAppRequest::class))]
    #[Permission(code: 'platform:platform-app:save')]
    #[ResultResponse(instance: new Result())]
    public function create(PlatformAppRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
            'updated_by' => $this->currentUser->id(),
        ]));

        return $this->success();
    }

    #[Put(
        path: '/admin/platform-app/{id}',
        operationId: 'platformAppSave',
        summary: '保存平台应用',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: PlatformAppRequest::class))]
    #[Permission(code: 'platform:platform-app:update')]
    #[ResultResponse(instance: new Result())]
    public function save(int $id, PlatformAppRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));

        return $this->success();
    }

    #[Put(
        path: '/admin/platform-app/{id}/enable',
        operationId: 'platformAppEnable',
        summary: '启用平台应用',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[Permission(code: 'platform:platform-app:disable')]
    #[ResultResponse(instance: new Result())]
    public function enable(int $id): Result
    {
        $this->service->enableById($id, $this->currentUser->id());
        return $this->success();
    }

    #[Put(
        path: '/admin/platform-app/{id}/disable',
        operationId: 'platformAppDisable',
        summary: '停用平台应用',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['平台应用管理'],
    )]
    #[Permission(code: 'platform:platform-app:disable')]
    #[ResultResponse(instance: new Result())]
    public function disable(int $id): Result
    {
        $this->service->disableById($id, $this->currentUser->id());
        return $this->success();
    }
}
