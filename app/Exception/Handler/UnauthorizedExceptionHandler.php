<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\Validation\UnauthorizedException;
use Throwable;

final class UnauthorizedExceptionHandler extends AbstractHandler
{
    public function handleResponse(Throwable $throwable): Result
    {
        return new Result(
            ResultCode::FORBIDDEN,
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof UnauthorizedException;
    }
}
