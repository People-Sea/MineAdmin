<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 主键
 * @property string $name 应用名称
 * @property string $app_id 腾讯广告应用ID
 * @property string $secret_ciphertext 加密后的应用密钥
 * @property string $callback_url OAuth回调地址
 * @property int $status 状态
 * @property string $availability_status 可用状态
 * @property Carbon|null $last_check_at 最近校验时间
 * @property string $last_error_code 最近错误码
 * @property string $last_error_message 最近错误信息
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 */
class PlatformApp extends Model
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 2;

    public const AVAILABILITY_UNKNOWN = 'unknown';

    public const AVAILABILITY_AVAILABLE = 'available';

    public const AVAILABILITY_UNAVAILABLE = 'unavailable';

    public const AVAILABILITY_PARTIAL = 'partial';

    protected ?string $table = 'platform_app';

    protected array $fillable = [
        'id',
        'name',
        'app_id',
        'secret_ciphertext',
        'callback_url',
        'status',
        'availability_status',
        'last_check_at',
        'last_error_code',
        'last_error_message',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'remark',
    ];

    protected array $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'last_check_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
