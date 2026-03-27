<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant', static function (Blueprint $table) {
            $table->comment('租户表');
            $table->bigIncrements('id')->comment('主键');
            $table->string('name', 60)->comment('租户名称');
            $table->string('code', 60)->comment('租户编码')->unique();
            $table->string('contact_name', 30)->default('')->comment('联系人');
            $table->string('contact_phone', 20)->default('')->comment('联系电话');
            $table->tinyInteger('status')->default(1)->comment('状态:1=正常,2=停用');
            $table->authorBy();
            $table->datetimes();
            $table->string('remark', 255)->default('')->comment('备注');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant');
    }
};
