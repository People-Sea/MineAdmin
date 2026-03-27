<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Tenant;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Tenant\TenantRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\TenantSchema;
use App\Service\Tenant\TenantService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Swagger\Annotation\Delete;
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
final class TenantController extends AbstractController
{
    public function __construct(
        private readonly TenantService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/tenant/list',
        operationId: 'tenantList',
        summary: '租户列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户管理'],
    )]
    #[PageResponse(instance: TenantSchema::class)]
    #[Permission(code: 'platform:tenant:index')]
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

    #[Post(
        path: '/admin/tenant',
        operationId: 'tenantCreate',
        summary: '创建租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantRequest::class))]
    #[Permission(code: 'platform:tenant:save')]
    #[ResultResponse(instance: new Result())]
    public function create(TenantRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Put(
        path: '/admin/tenant/{id}',
        operationId: 'tenantSave',
        summary: '保存租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantRequest::class))]
    #[Permission(code: 'platform:tenant:update')]
    #[ResultResponse(instance: new Result())]
    public function save(int $id, TenantRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Delete(
        path: '/admin/tenant',
        operationId: 'tenantDelete',
        summary: '删除租户',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户管理'],
    )]
    #[Permission(code: 'platform:tenant:delete')]
    #[ResultResponse(instance: new Result())]
    public function delete(): Result
    {
        $this->service->deleteById($this->getRequestData());
        return $this->success();
    }
}
