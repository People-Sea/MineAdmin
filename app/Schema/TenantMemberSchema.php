<?php

declare(strict_types=1);

namespace App\Schema;

use App\Model\Permission\User;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'TenantMemberSchema')]
final class TenantMemberSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'tenant_id', title: '租户ID', type: 'int')]
    public ?int $tenantId;

    #[Property(property: 'tenant_name', title: '租户名称', type: 'string')]
    public ?string $tenantName;

    #[Property(property: 'name', title: '成员姓名', type: 'string')]
    public ?string $name;

    #[Property(property: 'username', title: '登录账号', type: 'string')]
    public ?string $username;

    #[Property(property: 'email', title: '邮箱', type: 'string')]
    public ?string $email;

    #[Property(property: 'phone', title: '联系电话', type: 'string')]
    public ?string $phone;

    #[Property(property: 'role', title: '成员角色', type: 'string')]
    public ?string $role;

    #[Property(property: 'role_id', title: '成员角色ID', type: 'int')]
    public ?int $roleId;

    #[Property(property: 'role_label', title: '成员角色名称', type: 'string')]
    public ?string $roleLabel;

    #[Property(property: 'status', title: '状态', type: 'int')]
    public ?int $status;

    #[Property(property: 'project_ids', title: '项目ID集合', type: 'array')]
    public array $projectIds = [];

    #[Property(property: 'project_names', title: '项目名称集合', type: 'array')]
    public array $projectNames = [];

    #[Property(property: 'project_count', title: '项目数量', type: 'int')]
    public int $projectCount = 0;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string')]
    public ?string $updatedAt;

    #[Property(property: 'remark', title: '备注', type: 'string')]
    public ?string $remark;

    public function __construct(User $model)
    {
        $this->id = $model->id;
        $this->tenantId = $model->tenant_id;
        $this->tenantName = (string) ($model->tenant_name ?? '');
        $this->name = $model->nickname;
        $this->username = $model->username;
        $this->email = $model->email;
        $this->phone = $model->phone;
        $this->role = $model->role;
        $this->roleId = $model->role_id;
        $this->roleLabel = (string) ($model->role_label ?? '');
        $this->status = $model->status;
        $this->projectIds = (array) ($model->project_ids ?? []);
        $this->projectNames = (array) ($model->project_names ?? []);
        $this->projectCount = (int) ($model->project_count ?? 0);
        $this->updatedAt = $model->updated_at?->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        $this->remark = $model->remark;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'tenant_name' => $this->tenantName,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'role_id' => $this->roleId,
            'role_label' => $this->roleLabel,
            'status' => $this->status,
            'project_ids' => $this->projectIds,
            'project_names' => $this->projectNames,
            'project_count' => $this->projectCount,
            'updated_at' => $this->updatedAt,
            'remark' => $this->remark,
        ];
    }
}
