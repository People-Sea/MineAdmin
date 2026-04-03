<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Hyperf\Database\Model\ModelNotFoundException;
use Throwable;

final class ModeNotFoundHandler extends AbstractHandler
{
    public function handleResponse(Throwable $throwable): Result
    {
        $this->stopPropagation();
        return new Result(
            code: ResultCode::NOT_FOUND
        );
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ModelNotFoundException;
    }
}
