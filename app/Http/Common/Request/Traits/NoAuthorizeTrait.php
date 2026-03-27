<?php

declare(strict_types=1);

namespace App\Http\Common\Request\Traits;

trait NoAuthorizeTrait
{
    public function authorize(): bool
    {
        return true;
    }
}
