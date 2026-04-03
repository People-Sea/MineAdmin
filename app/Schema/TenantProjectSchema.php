<?php

declare(strict_types=1);

namespace App\Schema;

use App\Model\TenantProject;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;
use JsonSerializable;

#[Schema(title: 'TenantProjectSchema')]
final class TenantProjectSchema implements JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'tenant_id', title: '租户ID', type: 'int')]
    public ?int $tenantId;

    #[Property(property: 'tenant_name', title: '租户名称', type: 'string')]
    public ?string $tenantName;

    #[Property(property: 'name', title: '项目名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'is_default', title: '是否主项目', type: 'int')]
    public ?int $isDefault;

    #[Property(property: 'status', title: '状态', type: 'int')]
    public ?int $status;

    #[Property(property: 'member_ids', title: '成员ID集合', type: 'array')]
    public array $memberIds = [];

    #[Property(property: 'member_names', title: '成员名称集合', type: 'array')]
    public array $memberNames = [];

    #[Property(property: 'member_count', title: '成员数量', type: 'int')]
    public int $memberCount = 0;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string')]
    public ?string $updatedAt;

    #[Property(property: 'remark', title: '备注', type: 'string')]
    public ?string $remark;

    public function __construct(TenantProject $model)
    {
        $this->id = $model->id;
        $this->tenantId = $model->tenant_id;
        $this->tenantName = (string) ($model->tenant_name ?? '');
        $this->name = $model->name;
        $this->isDefault = $model->is_default;
        $this->status = $model->status;
        $this->memberIds = (array) ($model->member_ids ?? []);
        $this->memberNames = (array) ($model->member_names ?? []);
        $this->memberCount = (int) ($model->member_count ?? 0);
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
            'is_default' => $this->isDefault,
            'status' => $this->status,
            'member_ids' => $this->memberIds,
            'member_names' => $this->memberNames,
            'member_count' => $this->memberCount,
            'updated_at' => $this->updatedAt,
            'remark' => $this->remark,
        ];
    }
}
