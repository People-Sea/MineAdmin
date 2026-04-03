<?php

declare(strict_types=1);

namespace App\Library\DataPermission\Rule;

use Closure;
use RuntimeException;

class CustomFuncFactory
{
    /**
     * @var array<string,Closure>
     */
    private static array $customFunc = [];

    public static function registerCustomFunc(string $name, Closure $func): void
    {
        self::$customFunc[$name] = $func;
    }

    public static function getCustomFunc(string $name): Closure
    {
        if (isset(self::$customFunc[$name])) {
            return self::$customFunc[$name];
        }
        throw new RuntimeException('Custom func not found');
    }
}
