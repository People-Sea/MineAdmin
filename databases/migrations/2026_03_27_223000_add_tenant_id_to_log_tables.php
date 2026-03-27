<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_login_log', static function (Blueprint $table) {
            if (! Schema::hasColumn('user_login_log', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->comment('租户ID');
                $table->index('tenant_id', 'idx_user_login_log_tenant_id');
            }
        });

        Schema::table('user_operation_log', static function (Blueprint $table) {
            if (! Schema::hasColumn('user_operation_log', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->comment('租户ID');
                $table->index('tenant_id', 'idx_user_operation_log_tenant_id');
            }
        });

        $prefix = Db::connection()->getTablePrefix();

        if (Schema::hasTable('user_login_log')) {
            Db::statement(sprintf(
                'UPDATE `%suser_login_log` ul INNER JOIN `%suser` u ON u.username = ul.username SET ul.tenant_id = u.tenant_id WHERE ul.tenant_id IS NULL',
                $prefix,
                $prefix
            ));
        }

        if (Schema::hasTable('user_operation_log')) {
            Db::statement(sprintf(
                'UPDATE `%suser_operation_log` uo INNER JOIN `%suser` u ON u.username = uo.username SET uo.tenant_id = u.tenant_id WHERE uo.tenant_id IS NULL',
                $prefix,
                $prefix
            ));
        }
    }

    public function down(): void
    {
        Schema::table('user_login_log', static function (Blueprint $table) {
            if (Schema::hasColumn('user_login_log', 'tenant_id')) {
                $table->dropIndex('idx_user_login_log_tenant_id');
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('user_operation_log', static function (Blueprint $table) {
            if (Schema::hasColumn('user_operation_log', 'tenant_id')) {
                $table->dropIndex('idx_user_operation_log_tenant_id');
                $table->dropColumn('tenant_id');
            }
        });
    }
};
