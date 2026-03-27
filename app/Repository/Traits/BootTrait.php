<?php

declare(strict_types=1);

namespace App\Repository\Traits;

trait BootTrait
{
    protected function startBoot(...$params): void
    {
        $traits = class_uses_recursive(static::class);
        foreach ($traits as $trait) {
            $method = 'boot' . class_basename($trait);
            if (method_exists($this, $method)) {
                $this->{$method}(...$params);
            }
        }
    }
}
