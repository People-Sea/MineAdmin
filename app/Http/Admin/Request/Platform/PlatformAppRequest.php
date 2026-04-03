<?php

declare(strict_types=1);

namespace App\Http\Admin\Request\Platform;

use App\Http\Common\Request\Traits\HttpMethodTrait;
use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use App\Schema\PlatformAppSchema;
use Hyperf\Validation\Request\FormRequest;
use Mine\Swagger\Attributes\FormRequest as FormRequestAnnotation;

#[FormRequestAnnotation(
    schema: PlatformAppSchema::class,
    only: [
        'name',
        'app_id',
        'app_secret',
        'callback_url',
        'status',
        'remark',
    ]
)]
class PlatformAppRequest extends FormRequest
{
    use HttpMethodTrait;
    use NoAuthorizeTrait;

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:60',
            'app_id' => 'required|string|max:100',
            'app_secret' => 'nullable|string|max:500',
            'callback_url' => 'required|string|max:255|url',
            'status' => 'sometimes|integer|in:1,2',
            'remark' => 'nullable|string|max:255',
        ];

        if ($this->isCreate()) {
            $rules['app_id'] .= '|unique:platform_app,app_id';
            $rules['app_secret'] = 'required|string|max:500';
        }

        if ($this->isUpdate()) {
            $rules['app_id'] .= '|unique:platform_app,app_id,' . $this->route('id');
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => '应用名称',
            'app_id' => '应用ID',
            'app_secret' => '应用密钥',
            'callback_url' => '回调地址',
            'status' => '状态',
            'remark' => '备注',
        ];
    }
}
