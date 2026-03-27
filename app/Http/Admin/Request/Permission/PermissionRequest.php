<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Permission;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Schema\UserSchema;
use Hyperf\Validation\Request\FormRequest;

#[\Mine\Swagger\Attributes\FormRequest(
    schema: UserSchema::class,
    only: [
        'nickname', 'password', 'avatar', 'signed', 'backend_setting', 'last_project_id',
    ]
)]
class PermissionRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'nickname' => 'sometimes|string|max:255',
            'new_password' => 'sometimes|confirmed|string|min:8',
            'new_password_confirmation' => 'sometimes|string|min:8',
            'old_password' => ['sometimes', 'string'],
            'avatar' => 'sometimes|string|max:255',
            'signed' => 'sometimes|string|max:255',
            'backend_setting' => 'sometimes|array',
            'last_project_id' => 'sometimes|nullable|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'nickname' => trans('user.nickname'),
            'new_password' => trans('user.password'),
            'new_password_confirmation' => trans('user.password_confirmation'),
            'old_password' => trans('user.old_password'),
            'avatar' => trans('user.avatar'),
            'signed' => trans('user.signed'),
            'backend_setting' => trans('user.backend_setting'),
            'last_project_id' => '最近使用项目',
        ];
    }
}
