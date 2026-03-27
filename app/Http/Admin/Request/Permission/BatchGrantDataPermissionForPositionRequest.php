<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Permission;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Model\Enums\DataPermission\PolicyType;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class BatchGrantDataPermissionForPositionRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'policy_type' => [
                'required',
                'string',
                Rule::enum(PolicyType::class),
            ],
            'value' => [
                'sometimes',
                'array',
                'min:1',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'policy_type' => '策略类型',
            'value' => '策略值',
        ];
    }
}
