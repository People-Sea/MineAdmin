<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Tenant;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Tenant\TenantMemberRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Http\CurrentUser;
use App\Schema\TenantMemberSchema;
use App\Service\Tenant\TenantMemberService;
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
final class TenantMemberController extends AbstractController
{
    public function __construct(
        private readonly TenantMemberService $service,
        private readonly CurrentUser $currentUser
    ) {}

    #[Get(
        path: '/admin/tenant-member/list',
        operationId: 'tenantMemberList',
        summary: '租户成员列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[PageResponse(instance: TenantMemberSchema::class)]
    #[Permission(code: 'platform:tenant-member:index')]
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
        path: '/admin/tenant-member/options',
        operationId: 'tenantMemberOptions',
        summary: '租户成员选项列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[Permission(code: 'platform:tenant-member:index')]
    #[ResultResponse(instance: new Result())]
    public function options(): Result
    {
        return $this->success($this->service->options($this->getRequestData()));
    }

    #[Get(
        path: '/admin/tenant-member/role-options',
        operationId: 'tenantMemberRoleOptions',
        summary: '租户成员角色选项列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[Permission(code: 'platform:tenant-member:index')]
    #[ResultResponse(instance: new Result())]
    public function roleOptions(): Result
    {
        return $this->success($this->service->roleOptions());
    }

    #[Post(
        path: '/admin/tenant-member',
        operationId: 'tenantMemberCreate',
        summary: '创建租户成员',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantMemberRequest::class))]
    #[Permission(code: 'platform:tenant-member:save')]
    #[ResultResponse(instance: new Result())]
    public function create(TenantMemberRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->currentUser->id(),
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Put(
        path: '/admin/tenant-member/{id}',
        operationId: 'tenantMemberSave',
        summary: '保存租户成员',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[RequestBody(content: new JsonContent(ref: TenantMemberRequest::class))]
    #[Permission(code: 'platform:tenant-member:update')]
    #[ResultResponse(instance: new Result())]
    public function save(int $id, TenantMemberRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->currentUser->id(),
        ]));
        return $this->success();
    }

    #[Delete(
        path: '/admin/tenant-member',
        operationId: 'tenantMemberDelete',
        summary: '删除租户成员',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['租户成员管理'],
    )]
    #[Permission(code: 'platform:tenant-member:delete')]
    #[ResultResponse(instance: new Result())]
    public function delete(): Result
    {
        $this->service->deleteById($this->getRequestData());
        return $this->success();
    }
}
