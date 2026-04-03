<?php

declare(strict_types=1);

namespace App\Repository\Permission;

use App\Model\Permission\Role;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<Role>
 */
final class RoleRepository extends IRepository
{
    public function __construct(
        protected readonly Role $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(Arr::get($params, 'name'), static function (Builder $query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'code'), static function (Builder $query, $code) {
                $query->whereIn('code', Arr::wrap($code));
            })
            ->when(Arr::has($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', $params['status']);
            })
            ->when(Arr::get($params, 'created_at'), static function (Builder $query, $createdAt) {
                $query->whereBetween('created_at', $createdAt);
            });
    }
}
