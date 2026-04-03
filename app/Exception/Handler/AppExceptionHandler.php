<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Throwable;

final class AppExceptionHandler extends AbstractHandler
{
    public function handleResponse(Throwable $throwable): Result
    {
        $this->stopPropagation();
        return new Result(
            code: ResultCode::FAIL,
            message: $throwable->getMessage()
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
