<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Exception\BusinessException;
use App\Http\Common\Result;

final class BusinessExceptionHandler extends AbstractHandler
{
    /**
     * @param BusinessException $throwable
     */
    public function handleResponse(\Throwable $throwable): Result
    {
        $this->stopPropagation();
        return $throwable->getResponse();
    }

    public function isValid(\Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}
