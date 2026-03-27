<?php

declare(strict_types=1);

namespace App\Service\Tenant;

use App\Repository\Tenant\TenantRepository;
use App\Service\IService;

final class TenantService extends IService
{
    public function __construct(
        protected readonly TenantRepository $repository
    ) {}
}
