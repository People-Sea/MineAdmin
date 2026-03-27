<?php

declare(strict_types=1);
use App\Service\PassportService;
use Mine\JwtAuth\Interfaces\CheckTokenInterface;
use Mine\Upload\Factory;
use Mine\Upload\UploadInterface;

return [
    UploadInterface::class => Factory::class,
    CheckTokenInterface::class => PassportService::class,
];
