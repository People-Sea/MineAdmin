<?php

declare(strict_types=1);

namespace App\Model\Concern;

use App\Model\Scope\WorkspaceScope;

trait HasWorkspaceScope
{
    public static function bootHasWorkspaceScope(): void
    {
        static::addGlobalScope(new WorkspaceScope());
    }

    public function getWorkspaceTenantColumn(): ?string
    {
        return 'tenant_id';
    }

    public function getWorkspaceProjectColumn(): ?string
    {
        return null;
    }
}
