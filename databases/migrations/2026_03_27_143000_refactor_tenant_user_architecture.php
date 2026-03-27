<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        $this->addUserColumns();
        $this->createTenantProjectUserTable();
        $roleIds = $this->ensureTenantRoles();
        $memberUserMap = $this->migrateLegacyTenantMembers($roleIds);
        $this->migrateLegacyTenantProjectUsers($memberUserMap);
        $this->dropLegacyTables();
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_project_user')) {
            Schema::dropIfExists('tenant_project_user');
        }

        if (Schema::hasTable('user')) {
            Schema::table('user', static function (Blueprint $table) {
                if (Schema::hasColumn('user', 'tenant_id')) {
                    $table->dropIndex('idx_user_tenant_id');
                    $table->dropColumn('tenant_id');
                }

                if (Schema::hasColumn('user', 'last_project_id')) {
                    $table->dropIndex('idx_user_last_project_id');
                    $table->dropColumn('last_project_id');
                }
            });
        }
    }

    private function addUserColumns(): void
    {
        Schema::table('user', static function (Blueprint $table) {
            if (! Schema::hasColumn('user', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->comment('所属租户ID');
                $table->index('tenant_id', 'idx_user_tenant_id');
            }

            if (! Schema::hasColumn('user', 'last_project_id')) {
                $table->unsignedBigInteger('last_project_id')->nullable()->comment('最近使用项目ID');
                $table->index('last_project_id', 'idx_user_last_project_id');
            }
        });
    }

    private function createTenantProjectUserTable(): void
    {
        if (Schema::hasTable('tenant_project_user')) {
            return;
        }

        Schema::create('tenant_project_user', static function (Blueprint $table) {
            $table->comment('租户项目用户关系表');
            $table->bigIncrements('id')->comment('主键');
            $table->unsignedBigInteger('tenant_id')->comment('租户ID');
            $table->unsignedBigInteger('project_id')->comment('项目ID');
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->authorBy();
            $table->datetimes();

            $table->unique(['project_id', 'user_id'], 'uk_tenant_project_user');
            $table->index('tenant_id', 'idx_tenant_project_user_tenant_id');
            $table->index('project_id', 'idx_tenant_project_user_project_id');
            $table->index('user_id', 'idx_tenant_project_user_user_id');
        });
    }

    /**
     * @return array<string,int>
     */
    private function ensureTenantRoles(): array
    {
        $now = date('Y-m-d H:i:s');
        $roles = [
            'tenant_admin' => ['name' => '租户管理员', 'code' => 'TenantAdmin', 'remark' => '租户管理员'],
            'optimizer' => ['name' => '优化师', 'code' => 'Optimizer', 'remark' => '优化师'],
        ];

        $roleIds = [];
        foreach ($roles as $key => $role) {
            $roleId = Db::table('role')->where('code', $role['code'])->value('id');
            if ($roleId === null) {
                $roleId = Db::table('role')->insertGetId([
                    'name' => $role['name'],
                    'code' => $role['code'],
                    'status' => 1,
                    'sort' => 0,
                    'created_by' => 0,
                    'updated_by' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'remark' => $role['remark'],
                ]);
            }
            $roleIds[$key] = (int) $roleId;
        }

        return $roleIds;
    }

    /**
     * @param array<string,int> $roleIds
     * @return array<int,int>
     */
    private function migrateLegacyTenantMembers(array $roleIds): array
    {
        if (! Schema::hasTable('tenant_member')) {
            return [];
        }

        $members = Db::table('tenant_member')->orderBy('id')->get();
        $memberUserMap = [];

        foreach ($members as $member) {
            $now = date('Y-m-d H:i:s');
            $userId = Db::table('user')->insertGetId([
                'username' => sprintf('t%du%d', (int) $member->tenant_id, (int) $member->id),
                'password' => $member->password,
                'user_type' => '200',
                'tenant_id' => (int) $member->tenant_id,
                'last_project_id' => $member->last_project_id ? (int) $member->last_project_id : null,
                'nickname' => (string) $member->name,
                'phone' => (string) $member->phone,
                'email' => (string) $member->email,
                'avatar' => '',
                'signed' => '',
                'status' => (int) $member->status,
                'login_ip' => '127.0.0.1',
                'login_time' => $member->updated_at ?? $member->created_at ?? $now,
                'backend_setting' => null,
                'created_by' => (int) ($member->created_by ?? 0),
                'updated_by' => (int) ($member->updated_by ?? 0),
                'created_at' => $member->created_at ?? $now,
                'updated_at' => $member->updated_at ?? $now,
                'remark' => (string) ($member->remark ?? ''),
            ]);

            $memberUserMap[(int) $member->id] = (int) $userId;

            $roleId = $roleIds[(string) $member->role] ?? $roleIds['optimizer'];
            Db::table('user_belongs_role')->updateOrInsert([
                'user_id' => (int) $userId,
                'role_id' => (int) $roleId,
            ], []);
        }

        return $memberUserMap;
    }

    /**
     * @param array<int,int> $memberUserMap
     */
    private function migrateLegacyTenantProjectUsers(array $memberUserMap): void
    {
        if (! Schema::hasTable('tenant_project_member')) {
            return;
        }

        $records = Db::table('tenant_project_member')->get();
        foreach ($records as $record) {
            $userId = $memberUserMap[(int) $record->member_id] ?? null;
            if ($userId === null) {
                continue;
            }

            Db::table('tenant_project_user')->updateOrInsert([
                'project_id' => (int) $record->project_id,
                'user_id' => (int) $userId,
            ], [
                'tenant_id' => (int) $record->tenant_id,
                'created_by' => (int) ($record->created_by ?? 0),
                'updated_by' => (int) ($record->updated_by ?? 0),
                'created_at' => $record->created_at ?? date('Y-m-d H:i:s'),
                'updated_at' => $record->updated_at ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function dropLegacyTables(): void
    {
        if (Schema::hasTable('tenant_project_member')) {
            Schema::drop('tenant_project_member');
        }

        if (Schema::hasTable('tenant_member')) {
            Schema::drop('tenant_member');
        }
    }
};
