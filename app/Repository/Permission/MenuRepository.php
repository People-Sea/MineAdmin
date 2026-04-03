<?php

declare(strict_types=1);

namespace App\Repository\Permission;

use App\Model\Enums\User\Status;
use App\Model\Permission\Menu;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;

/**
 * @extends IRepository<Menu>
 */
final class MenuRepository extends IRepository
{
    public function __construct(
        protected readonly Menu $model
    ) {}

    public function enablePageOrderBy(): bool
    {
        return false;
    }

    /**
     * @return \Hyperf\Collection\Collection<int, Menu>
     */
    public function list(array $params = []): \Hyperf\Collection\Collection
    {
        $query = $this->perQuery($this->getQuery(), $params);
        $query->orderBy('sort');

        return $query->get();
    }

    public function handleSearch(Builder $query, array $params): Builder
    {
        $whereInName = static function (Builder $query, array|string $code) {
            $query->whereIn('name', Arr::wrap($code));
        };
        return $query
            ->when(Arr::get($params, 'sortable'), static function (Builder $query, array $sortable) {
                $query->orderBy(key($sortable), current($sortable));
            })
            ->when(Arr::get($params, 'code'), $whereInName)
            ->when(Arr::get($params, 'name'), $whereInName)
            ->when(Arr::get($params, 'children'), static function (Builder $query) {
                $query->with('children');
            })->when(Arr::get($params, 'status'), static function (Builder $query, Status $status) {
                $query->where('status', $status);
            })
            ->when(Arr::has($params, 'parent_id'), static function (Builder $query) use ($params) {
                $query->where('parent_id', Arr::get($params, 'parent_id'));
            });
    }

    public function allTree(): Collection
    {
        return $this->model
            ->newQuery()
            ->where('parent_id', 0)
            ->with('children')
            ->get();
    }
}
