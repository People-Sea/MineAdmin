<?php

declare(strict_types=1);

namespace App\Repository\Tenant;

use App\Model\Tenant;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<Tenant>
 */
final class TenantRepository extends IRepository
{
    public function __construct(
        protected readonly Tenant $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->withCount(['members', 'projects'])
            ->when(Arr::get($params, 'name'), static function (Builder $query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'code'), static function (Builder $query, $code) {
                $query->where('code', 'like', '%' . $code . '%');
            })
            ->when(Arr::get($params, 'contact_name'), static function (Builder $query, $contactName) {
                $query->where('contact_name', 'like', '%' . $contactName . '%');
            })
            ->when(Arr::get($params, 'contact_phone'), static function (Builder $query, $contactPhone) {
                $query->where('contact_phone', 'like', '%' . $contactPhone . '%');
            })
            ->when(Arr::exists($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', Arr::get($params, 'status'));
            })
            ->when(Arr::exists($params, 'created_at'), static function (Builder $query) use ($params) {
                $query->whereBetween('created_at', [
                    Arr::get($params, 'created_at')[0] . ' 00:00:00',
                    Arr::get($params, 'created_at')[1] . ' 23:59:59',
                ]);
            })
            ->orderByDesc('id');
    }
}
