<?php

declare(strict_types=1);

namespace App\Schema;

use App\Model\PlatformApp;
use Carbon\CarbonInterface;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;
use JsonSerializable;

#[Schema(title: 'PlatformAppSchema')]
final class PlatformAppSchema implements JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'name', title: '应用名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'app_id', title: '应用ID', type: 'string')]
    public ?string $appId;

    #[Property(property: 'app_secret', title: '应用密钥', type: 'string')]
    public ?string $appSecret = null;

    #[Property(property: 'callback_url', title: '回调地址', type: 'string')]
    public ?string $callbackUrl;

    #[Property(property: 'status', title: '状态', type: 'int')]
    public ?int $status;

    #[Property(property: 'availability_status', title: '可用状态', type: 'string')]
    public ?string $availabilityStatus;

    #[Property(property: 'last_check_at', title: '最近校验时间', type: 'string')]
    public ?string $lastCheckAt;

    #[Property(property: 'last_error_code', title: '最近错误码', type: 'string')]
    public ?string $lastErrorCode;

    #[Property(property: 'last_error_message', title: '最近错误信息', type: 'string')]
    public ?string $lastErrorMessage;

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

    public function __construct(PlatformApp $model)
    {
        $this->id = $model->id;
        $this->name = $model->name;
        $this->appId = $model->app_id;
        $this->callbackUrl = $model->callback_url;
        $this->status = $model->status;
        $this->availabilityStatus = $model->availability_status;
        $this->lastCheckAt = $model->last_check_at?->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
        $this->lastErrorCode = $model->last_error_code;
        $this->lastErrorMessage = $model->last_error_message;
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
            'app_id' => $this->appId,
            'callback_url' => $this->callbackUrl,
            'status' => $this->status,
            'availability_status' => $this->availabilityStatus,
            'last_check_at' => $this->lastCheckAt,
            'last_error_code' => $this->lastErrorCode,
            'last_error_message' => $this->lastErrorMessage,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'remark' => $this->remark,
        ];
    }
}
