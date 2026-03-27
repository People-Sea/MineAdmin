<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Concern\HasWorkspaceScope;
use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property null|int $tenant_id 租户ID
 * @property string $username 用户名
 * @property string $method 请求方式
 * @property string $router 请求路由
 * @property string $service_name 业务名称
 * @property string $ip 请求IP地址
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 */
class UserOperationLog extends Model
{
    use HasWorkspaceScope;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_operation_log';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'tenant_id', 'username', 'method', 'router', 'service_name', 'ip', 'ip_location', 'created_at', 'updated_at', 'remark'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'tenant_id' => 'integer', 'created_by' => 'integer', 'updated_by' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
