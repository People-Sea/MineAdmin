<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Enums\User\Type;
use App\Model\Permission\User;
use Carbon\Carbon;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 主键
 * @property string $name 租户名称
 * @property string $code 租户编码
 * @property string $contact_name 联系人
 * @property string $contact_phone 联系电话
 * @property int $status 状态 (1正常 2停用)
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 */
class Tenant extends Model
{
    protected ?string $table = 'tenant';

    protected array $fillable = [
        'id',
        'name',
        'code',
        'contact_name',
        'contact_phone',
        'status',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function members(): HasMany
    {
        $relation = $this->hasMany(User::class, 'tenant_id', 'id');
        $relation->getQuery()->where('user_type', Type::USER);

        return $relation;
    }

    public function projects(): HasMany
    {
        return $this->hasMany(TenantProject::class, 'tenant_id', 'id');
    }
}
