<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Tenant;

use App\Http\Common\Request\Traits\HttpMethodTrait;
use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Model\Permission\User;
use App\Schema\TenantMemberSchema;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;
use Mine\Swagger\Attributes\FormRequest as FormRequestAnnotation;

#[FormRequestAnnotation(
    schema: TenantMemberSchema::class,
    only: [
        'tenant_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'remark',
    ]
)]
final class TenantMemberRequest extends FormRequest
{
    use HttpMethodTrait;
    use NoAuthorizeTrait;

    public function rules(): array
    {
        $tenantId = (int) $this->input('tenant_id');
        if ($this->isUpdate() && $tenantId === 0) {
            /** @var null|User $member */
            $member = User::query()->find($this->route('id'));
            $tenantId = $member?->tenant_id ?? 0;
        }

        $usernameRule = Rule::unique('user', 'username');
        if ($this->isUpdate()) {
            $usernameRule = $usernameRule->ignore((int) $this->route('id'));
        }

        $rules = [
            'tenant_id' => 'required|integer|exists:tenant,id',
            'name' => 'required|string|max:30',
            'username' => [
                'required',
                'string',
                'max:20',
                $usernameRule,
            ],
            'email' => 'nullable|string|max:100|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|max:100',
            'role_id' => [
                'required',
                'integer',
                Rule::exists('role', 'id')->where(static function ($query) {
                    $query->whereIn(
                        'code',
                        array_map(
                            'strval',
                            (array) config('tenant.assignable_role_codes', ['TenantAdmin', 'Optimizer'])
                        )
                    );
                }),
            ],
            'status' => 'sometimes|integer|in:1,2',
            'remark' => 'nullable|string|max:255',
        ];

        if ($this->isCreate()) {
            $rules['password'] = 'required|string|min:6|max:100';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'tenant_id' => '租户',
            'name' => '成员姓名',
            'username' => '登录账号',
            'email' => '邮箱',
            'phone' => '联系电话',
            'password' => '登录密码',
            'role_id' => '成员角色',
            'status' => '状态',
            'remark' => '备注',
        ];
    }
}
