<?php

declare(strict_types=1);
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_belongs_menu', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('role_id')->comment('角色id');
            $table->bigInteger('menu_id')->comment('菜单id');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_belongs_menu');
    }
};
