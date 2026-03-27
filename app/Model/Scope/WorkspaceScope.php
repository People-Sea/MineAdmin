<?php

declare(strict_types=1);

namespace App\Model\Scope;

use App\Http\CurrentUser;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;

final class WorkspaceScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = CurrentUser::ctxUser();
        if ($user === null || ! $user->isTenantUser()) {
            return;
        }

        if (method_exists($model, 'getWorkspaceTenantColumn')) {
            $tenantColumn = $model->getWorkspaceTenantColumn();
            if ($tenantColumn !== null) {
                $builder->where($model->qualifyColumn($tenantColumn), (int) $user->tenant_id);
            }
        }

        if (method_exists($model, 'getWorkspaceProjectColumn')) {
            $projectColumn = $model->getWorkspaceProjectColumn();
            $projectId = (int) ($user->last_project_id ?? 0);
            if ($projectColumn !== null && $projectId > 0) {
                $builder->where($model->qualifyColumn($projectColumn), $projectId);
            }
        }
    }
}
