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
        'admin_name',
        'admin_username',
        'admin_email',
        'admin_phone',
        'admin_password',
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
            'admin_name' => 'sometimes|string|max:30',
            'admin_username' => 'sometimes|string|max:20|unique:user,username',
            'admin_email' => 'nullable|string|max:100|email',
            'admin_phone' => 'nullable|string|max:20',
            'admin_password' => 'sometimes|string|min:6|max:100',
        ];

        if ($this->isCreate()) {
            $rules['code'][] = 'unique:tenant,code';
            $rules['admin_name'] = 'required|string|max:30';
            $rules['admin_username'] = 'required|string|max:20|unique:user,username';
            $rules['admin_password'] = 'required|string|min:6|max:100';
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
            'admin_name' => '租户管理员姓名',
            'admin_username' => '租户管理员账号',
            'admin_email' => '租户管理员邮箱',
            'admin_phone' => '租户管理员电话',
            'admin_password' => '租户管理员密码',
            'status' => '状态',
            'remark' => '备注',
        ];
    }
}
