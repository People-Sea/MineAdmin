<?php

declare(strict_types=1);

namespace App\Service\Tenant;

use App\Exception\BusinessException;
use App\Http\CurrentUser;
use App\Http\Common\ResultCode;
use App\Model\Enums\User\Type;
use App\Model\Permission\User;
use App\Model\TenantProject;
use App\Repository\Tenant\TenantProjectRepository;
use App\Service\IService;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Db;

/**
 * @extends IService<TenantProject>
 */
final class TenantProjectService extends IService
{
    public function __construct(
        protected readonly TenantProjectRepository $repository,
        private readonly CurrentUser $currentUser
    ) {}

    public function page(array $params, int $page = 1, int $pageSize = 10): array
    {
        return parent::page($this->applyScope($params), $page, $pageSize);
    }

    public function options(array $params = []): Collection
    {
        return $this->repository->list($this->applyScope($params))->map(static function (TenantProject $project) {
            return [
                'id' => $project->id,
                'tenant_id' => $project->tenant_id,
                'name' => $project->name,
                'is_default' => $project->is_default,
                'status' => $project->status,
            ];
        });
    }

    public function create(array $data): mixed
    {
        return Db::transaction(function () use ($data) {
            $this->ensureTenantAdminCanManage();
            $memberIds = $data['member_ids'] ?? [];
            unset($data['member_ids']);
            $data['tenant_id'] = $this->resolveTenantId($data['tenant_id'] ?? null);

            /** @var TenantProject $project */
            $project = parent::create($data);
            $this->syncMembers($project, $memberIds, (int) ($data['updated_by'] ?? $data['created_by'] ?? 0));
            return $project;
        });
    }

    public function updateById(mixed $id, array $data): mixed
    {
        return Db::transaction(function () use ($id, $data) {
            $this->ensureTenantAdminCanManage();
            /** @var TenantProject $project */
            $project = $this->findProjectOrFail((int) $id);

            $memberIds = $data['member_ids'] ?? [];
            unset($data['member_ids']);
            if (isset($data['tenant_id'])) {
                $data['tenant_id'] = $this->resolveTenantId($data['tenant_id']);
            }

            $project->fill($data)->save();
            $this->syncMembers($project, $memberIds, (int) ($data['updated_by'] ?? $data['created_by'] ?? 0));
            return $project;
        });
    }

    public function deleteById(mixed $id): int
    {
        return Db::transaction(function () use ($id) {
            $this->ensureTenantAdminCanManage();
            $ids = is_array($id) ? $id : [$id];
            $projects = TenantProject::query()
                ->whereIn('id', $ids)
                ->when($this->currentUser->isTenantUser(), function ($query) {
                    $query->where('tenant_id', $this->currentUser->tenantId());
                })
                ->get();

            if ($projects->where('is_default', 1)->isNotEmpty()) {
                throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '主项目不能删除');
            }

            $projects->each(function (TenantProject $project) {
                User::query()
                    ->where('user_type', Type::USER)
                    ->where('tenant_id', $project->tenant_id)
                    ->where('last_project_id', $project->id)
                    ->update(['last_project_id' => $this->resolveFallbackProjectId($project)]);
                $project->delete();
            });

            return count($ids);
        });
    }

    private function syncMembers(TenantProject $project, array $memberIds, int $operatorId): void
    {
        if ($memberIds === []) {
            $project->members()->sync([]);
            return;
        }

        $validIds = User::query()
            ->where('tenant_id', $project->tenant_id)
            ->where('user_type', Type::USER)
            ->whereIn('id', $memberIds)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if (count($validIds) !== count(array_unique($memberIds))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '存在不属于当前租户的成员，无法加入项目');
        }

        $syncData = [];
        foreach ($validIds as $memberId) {
            $syncData[$memberId] = [
                'tenant_id' => $project->tenant_id,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ];
        }

        $project->members()->sync($syncData);
    }

    private function applyScope(array $params): array
    {
        if ($this->currentUser->isTenantUser()) {
            $params['tenant_id'] = $this->currentUser->tenantId();
            if (! $this->currentUser->isTenantAdmin()) {
                $params['member_id'] = $this->currentUser->id();
            }
        }

        return $params;
    }

    private function ensureTenantAdminCanManage(): void
    {
        if ($this->currentUser->isTenantUser() && ! $this->currentUser->isTenantAdmin()) {
            throw new BusinessException(ResultCode::FORBIDDEN);
        }
    }

    private function resolveTenantId(int|null $tenantId): int
    {
        if ($this->currentUser->isTenantUser()) {
            return $this->currentUser->tenantId();
        }

        return (int) $tenantId;
    }

    private function findProjectOrFail(int $id): TenantProject
    {
        /** @var null|TenantProject $project */
        $project = TenantProject::query()
            ->whereKey($id)
            ->when($this->currentUser->isTenantUser(), function ($query) {
                $query->where('tenant_id', $this->currentUser->tenantId());
            })
            ->first();

        if ($project === null) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $project;
    }

    private function resolveFallbackProjectId(TenantProject $project): int|null
    {
        /** @var null|TenantProject $defaultProject */
        $defaultProject = TenantProject::query()
            ->where('tenant_id', $project->tenant_id)
            ->where('is_default', 1)
            ->first();

        return $defaultProject?->id;
    }
}
