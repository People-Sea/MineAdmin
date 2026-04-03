<?php

declare(strict_types=1);

namespace App\Schema;

use App\Model\Tenant;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;
use JsonSerializable;

#[Schema(title: 'TenantSchema')]
final class TenantSchema implements JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'name', title: '租户名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'code', title: '租户编码', type: 'string')]
    public ?string $code;

    #[Property(property: 'contact_name', title: '联系人', type: 'string')]
    public ?string $contactName;

    #[Property(property: 'contact_phone', title: '联系电话', type: 'string')]
    public ?string $contactPhone;

    #[Property(property: 'status', title: '状态 (1正常 2停用)', type: 'int')]
    public ?int $status;

    #[Property(property: 'member_count', title: '成员数量', type: 'int')]
    public ?int $memberCount;

    #[Property(property: 'project_count', title: '项目数量', type: 'int')]
    public ?int $projectCount;

    #[Property(property: 'created_by', title: '创建者', type: 'int')]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: '更新者', type: 'int')]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: '创建时间', type: 'string')]
    public mixed $createdAt;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string')]
    public ?string $updatedAt;

    #[Property(property: 'remark', title: '备注', type: 'string')]
    public ?string $remark;

    public function __construct(Tenant $model)
    {
        $this->id = $model->id;
        $this->name = $model->name;
        $this->code = $model->code;
        $this->contactName = $model->contact_name;
        $this->contactPhone = $model->contact_phone;
        $this->status = $model->status;
        $this->memberCount = (int) ($model->member_count ?? 0);
        $this->projectCount = (int) ($model->project_count ?? 0);
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $model->created_at;
        $this->updatedAt = $model->updated_at->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        $this->remark = $model->remark;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'status' => $this->status,
            'member_count' => $this->memberCount,
            'project_count' => $this->projectCount,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'remark' => $this->remark,
        ];
    }
}
