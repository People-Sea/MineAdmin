<?php

declare(strict_types=1);

namespace App\Http\Common\Request\Traits;

use Hyperf\Validation\Request\FormRequest;

/**
 * @mixin FormRequest
 */
trait HttpMethodTrait
{
    public function isCreate(): bool
    {
        return $this->isMethod('POST');
    }

    public function isUpdate(): bool
    {
        return $this->isMethod('PUT') || $this->isMethod('PATCH');
    }

    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    public function isSearch(): bool
    {
        return $this->isMethod('GET');
    }
}
