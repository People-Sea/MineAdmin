<?php

declare(strict_types=1);

namespace App\Model\Concern;

use App\Model\Scope\WorkspaceScope;
use Hyperf\Database\Schema\Schema;

trait HasWorkspaceScope
{
    /**
     * @var array<string,null|string>
     */
    protected static array $workspaceProjectColumnCache = [];

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
        $table = (string) $this->getTable();
        if ($table === '') {
            return null;
        }

        if (array_key_exists($table, static::$workspaceProjectColumnCache)) {
            return static::$workspaceProjectColumnCache[$table];
        }

        static::$workspaceProjectColumnCache[$table] = Schema::hasColumn($table, 'project_id')
            ? 'project_id'
            : null;

        return static::$workspaceProjectColumnCache[$table];
    }
}
