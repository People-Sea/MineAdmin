<?php

declare(strict_types=1);

namespace App\Model\Enums\User;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum Status: int
{
    use EnumConstantsTrait;

    #[Message('user.enums.status.1')]
    case Normal = 1;

    #[Message('user.enums.status.2')]
    case DISABLE = 2;

    public function isNormal(): bool
    {
        return $this === self::Normal;
    }

    public function isDisable(): bool
    {
        return $this === self::DISABLE;
    }
}
