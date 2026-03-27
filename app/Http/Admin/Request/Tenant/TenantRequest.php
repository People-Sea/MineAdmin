<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Tenant;

use App\Http\Common\Request\Traits\HttpMethodTrait;
use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Schema\TenantSchema;
use Hyperf\Validation\Request\FormRequest;
use Mine\Swagger\Attributes\FormRequest as FormRequestAnnotation;

#[FormRequestAnnotation(
    schema: TenantSchema::class,
    only: [
        'name',
        'code',
        'contact_name',
        'contact_phone',
        'status',
        'remark',
    ]
)]
class TenantRequest extends FormRequest
{
    use HttpMethodTrait;
    use NoAuthorizeTrait;

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:60',
            'code' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
            'contact_name' => 'nullable|string|max:30',
            'contact_phone' => 'nullable|string|max:20',
            'status' => 'sometimes|integer|in:1,2',
            'remark' => 'nullable|string|max:255',
        ];

        if ($this->isCreate()) {
            $rules['code'][] = 'unique:tenant,code';
        }
        if ($this->isUpdate()) {
            $rules['code'][] = 'unique:tenant,code,' . $this->route('id');
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => '租户名称',
            'code' => '租户编码',
            'contact_name' => '联系人',
            'contact_phone' => '联系电话',
            'status' => '状态',
            'remark' => '备注',
        ];
    }
}
