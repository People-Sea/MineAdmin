<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Tenant;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Http\CurrentUser;
use App\Model\TenantProject;
use App\Schema\TenantProjectSchema;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;
use Mine\Swagger\Attributes\FormRequest as FormRequestAnnotation;

#[FormRequestAnnotation(
    schema: TenantProjectSchema::class,
    only: [
        'tenant_id',
        'name',
        'status',
        'remark',
        'member_ids',
    ]
)]
final class TenantProjectRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        $currentUser = CurrentUser::ctxUser();
        $isTenantUser = $currentUser?->isTenantUser() ?? false;
        $tenantId = (int) $this->input('tenant_id');
        if ($this->isMethod('POST') && $tenantId === 0 && $isTenantUser) {
            $tenantId = (int) $currentUser->tenant_id;
        }

        if ($this->isMethod('PUT') && $tenantId === 0) {
            /** @var null|TenantProject $project */
            $project = TenantProject::query()->find($this->route('id'));
            $tenantId = (int) $project?->tenant_id;
        }

        $nameRule = Rule::unique('tenant_project', 'name')->where(static function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        });
        if ($this->isMethod('PUT')) {
            $nameRule = $nameRule->ignore((int) $this->route('id'));
        }

        return [
            'tenant_id' => $this->isMethod('POST') && ! $isTenantUser
                ? 'required|integer|exists:tenant,id'
                : 'sometimes|integer|exists:tenant,id',
            'name' => [
                'required',
                'string',
                'max:60',
                $nameRule,
            ],
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'sometimes|integer|exists:user,id',
            'status' => 'sometimes|integer|in:1,2',
            'remark' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'tenant_id' => '租户',
            'name' => '项目名称',
            'member_ids' => '项目成员',
            'status' => '状态',
            'remark' => '备注',
        ];
    }
}
