<?php

declare(strict_types=1);

namespace App\Library\DataPermission\Scope;

use Hyperf\DbConnection\Model\Model;

/**
 * @internal
 * @mixin Model
 */
trait DataScopes
{
    public static function bootDataScopes(): void
    {
        static::addGlobalScope(new DataScope());
    }
}
