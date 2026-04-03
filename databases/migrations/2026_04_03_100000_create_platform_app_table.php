<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_app', static function (Blueprint $table) {
            $table->comment('平台应用表');
            $table->bigIncrements('id')->comment('主键');
            $table->string('name', 60)->comment('应用名称');
            $table->string('app_id', 100)->comment('腾讯广告应用ID')->unique();
            $table->text('secret_ciphertext')->comment('加密后的应用密钥');
            $table->string('callback_url', 255)->comment('OAuth回调地址');
            $table->tinyInteger('status')->default(1)->comment('状态:1=启用,2=停用');
            $table->string('availability_status', 20)->default('unknown')->comment('可用状态');
            $table->dateTime('last_check_at')->nullable()->comment('最近校验时间');
            $table->string('last_error_code', 100)->default('')->comment('最近错误码');
            $table->string('last_error_message', 255)->default('')->comment('最近错误信息');
            $table->authorBy();
            $table->datetimes();
            $table->string('remark', 255)->default('')->comment('备注');

            $table->index('status', 'idx_platform_app_status');
            $table->index('availability_status', 'idx_platform_app_availability_status');
            $table->index('last_check_at', 'idx_platform_app_last_check_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_app');
    }
};
