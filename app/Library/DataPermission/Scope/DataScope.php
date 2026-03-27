<?php

declare(strict_types=1);

namespace App\Library\DataPermission\Scope;

use App\Http\CurrentUser;
use App\Library\DataPermission\Factory;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;

class DataScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = CurrentUser::ctxUser();
        if (empty($user)) {
            return;
        }

        Factory::make()->build($builder->getQuery(), $user);
    }
}
