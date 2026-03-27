<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_project', static function (Blueprint $table) {
            $table->comment('租户项目表');
            $table->bigIncrements('id')->comment('主键');
            $table->unsignedBigInteger('tenant_id')->comment('租户ID');
            $table->string('name', 60)->comment('项目名称');
            $table->tinyInteger('is_default')->default(0)->comment('是否主项目:1=是,0=否');
            $table->tinyInteger('status')->default(1)->comment('状态:1=正常,2=停用');
            $table->authorBy();
            $table->datetimes();
            $table->string('remark', 255)->default('')->comment('备注');

            $table->unique(['tenant_id', 'name'], 'uk_tenant_project_tenant_name');
            $table->index('tenant_id', 'idx_tenant_project_tenant_id');
            $table->index('is_default', 'idx_tenant_project_is_default');
            $table->index('status', 'idx_tenant_project_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_project');
    }
};
