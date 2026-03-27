<?php

declare(strict_types=1);

namespace App\Http\Common\Event;

class RequestOperationEvent
{
    public function __construct(
        private readonly int $userId,
        private readonly string $operation,
        private readonly string $path,
        private readonly string $ip,
        private readonly string $method = 'GET',
        private readonly string $remark = ''
    ) {}

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
