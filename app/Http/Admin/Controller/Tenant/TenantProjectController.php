<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Tenant;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Tenant\TenantProjectRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\TenantProjectSchema;
use App\Service\Tenant\TenantProjectService;
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
final class TenantProjectController extends AbstractController
{
    public function __construct(
        private readonly TenantProjectService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/tenant-project/list',
        operationId: 'tenantProjectList',
        summary: '租户项目列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户项目管理'],
    )]
    #[PageResponse(instance: TenantProjectSchema::class)]
    #[Permission(code: 'platform:tenant-project:index')]
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
        path: '/admin/tenant-project/options',
        operationId: 'tenantProjectOptions',
        summary: '租户项目选项列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户项目管理'],
    )]
    #[Permission(code: 'platform:tenant-project:index')]
    #[ResultResponse(instance: new Result())]
    public function options(): Result
    {
        return $this->success($this->service->options($this->getRequestData()));
    }

    #[Post(
        path: '/admin/tenant-project',
        operationId: 'tenantProjectCreate',
        summary: '创建租户项目',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户项目管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantProjectRequest::class))]
    #[Permission(code: 'platform:tenant-project:save')]
    #[ResultResponse(instance: new Result())]
    public function create(TenantProjectRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Put(
        path: '/admin/tenant-project/{id}',
        operationId: 'tenantProjectSave',
        summary: '保存租户项目',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户项目管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantProjectRequest::class))]
    #[Permission(code: 'platform:tenant-project:update')]
    #[ResultResponse(instance: new Result())]
    public function save(int $id, TenantProjectRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Delete(
        path: '/admin/tenant-project',
        operationId: 'tenantProjectDelete',
        summary: '删除租户项目',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户项目管理'],
    )]
    #[Permission(code: 'platform:tenant-project:delete')]
    #[ResultResponse(instance: new Result())]
    public function delete(): Result
    {
        $this->service->deleteById($this->getRequestData());
        return $this->success();
    }
}
