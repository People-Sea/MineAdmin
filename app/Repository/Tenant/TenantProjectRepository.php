<?php

declare(strict_types=1);

namespace App\Repository\Tenant;

use App\Model\TenantProject;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<TenantProject>
 */
final class TenantProjectRepository extends IRepository
{
    public function __construct(
        protected readonly TenantProject $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $memberModel = (new TenantProject())->members()->getRelated();

        return $query
            ->with([
                'tenant:id,name',
                'members' => static function ($query) use ($memberModel) {
                    $query->select([
                        $memberModel->qualifyColumn('id'),
                        $memberModel->qualifyColumn('nickname'),
                        $memberModel->qualifyColumn('status'),
                    ]);
                },
            ])
            ->when(Arr::get($params, 'tenant_id'), static function (Builder $query, $tenantId) {
                $query->where('tenant_id', (int) $tenantId);
            })
            ->when(Arr::get($params, 'name'), static function (Builder $query, $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::exists($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', Arr::get($params, 'status'));
            })
            ->when(Arr::exists($params, 'is_default'), static function (Builder $query) use ($params) {
                $query->where('is_default', Arr::get($params, 'is_default'));
            })
            ->when(Arr::get($params, 'member_id'), static function (Builder $query, $memberId) {
                $query->whereHas('members', static function (Builder $query) use ($memberId) {
                    $query->whereKey((int) $memberId);
                });
            })
            ->orderByDesc('is_default')
            ->orderByDesc('id');
    }

    public function handleItems(Collection $items): Collection
    {
        return $items->map(static function (TenantProject $item) {
            $members = $item->members ?? collect();

            $item->setAttribute('tenant_name', $item->tenant?->name ?? '');
            $item->setAttribute('member_ids', $members->pluck('id')->values()->all());
            $item->setAttribute('member_names', $members->pluck('nickname')->values()->all());
            $item->setAttribute('member_count', $members->count());

            return $item;
        });
    }
}
