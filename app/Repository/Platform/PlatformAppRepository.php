<?php

declare(strict_types=1);

namespace App\Repository\Platform;

use App\Model\PlatformApp;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Collection;

/**
 * @extends IRepository<PlatformApp>
 */
final class PlatformAppRepository extends IRepository
{
    public function __construct(
        protected readonly PlatformApp $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query
            ->when(Arr::get($params, 'name'), static function (Builder $query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'app_id'), static function (Builder $query, $appId) {
                $query->where('app_id', 'like', '%' . $appId . '%');
            })
            ->when(Arr::exists($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', Arr::get($params, 'status'));
            })
            ->when(Arr::get($params, 'availability_status'), static function (Builder $query, $availabilityStatus) {
                $query->where('availability_status', $availabilityStatus);
            })
            ->orderByDesc('id');
    }

    public function listEnabled(): Collection
    {
        return $this->getQuery()
            ->where('status', PlatformApp::STATUS_ENABLED)
            ->orderByDesc('id')
            ->get();
    }
}
