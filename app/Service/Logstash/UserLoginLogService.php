<?php

declare(strict_types=1);

namespace App\Service\Logstash;

use App\Repository\Logstash\UserLoginLogRepository;
use App\Service\IService;

final class UserLoginLogService extends IService
{
    public function __construct(
        protected readonly UserLoginLogRepository $repository
    ) {}
}
