<?php

declare(strict_types=1);

use App\Http\Admin\Controller\AttachmentController;
use App\Http\Admin\Controller\Logstash\UserLoginLogController;
use App\Http\Admin\Controller\Logstash\UserOperationLogController;
use App\Http\Admin\Controller\PassportController;
use App\Http\Admin\Controller\PermissionController;

return [
    'assignable_role_codes' => [
        'TenantAdmin',
        'Optimizer',
    ],
    'admin_role_code' => 'TenantAdmin',
    'allowed_controllers' => [
        PassportController::class,
        PermissionController::class,
        AttachmentController::class,
        UserLoginLogController::class,
        UserOperationLogController::class,
    ],
    'allowed_controller_prefixes' => [
        'App\\Http\\Admin\\Controller\\Tenant\\',
    ],
    'allowed_menu_names' => [
        'config',
        'log',
    ],
    'allowed_menu_prefixes' => [
        'platform:tenant-member',
        'platform:tenant-project',
        'log:userLogin',
        'log:userOperation',
    ],
];
