<?php

declare(strict_types=1);

namespace App\Service\Logstash;

use App\Repository\Logstash\UserOperationLogRepository;
use App\Service\IService;

final class UserOperationLogService extends IService
{
    public function __construct(
        protected readonly UserOperationLogRepository $repository
    ) {}
}
