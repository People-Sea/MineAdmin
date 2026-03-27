<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Concern\HasWorkspaceScope;
use App\Model\Permission\User;
use Carbon\Carbon;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id 主键
 * @property int $tenant_id 租户ID
 * @property string $name 项目名称
 * @property int $is_default 是否主项目
 * @property int $status 状态
 * @property int $created_by 创建者
 * @property int $updated_by 更新者
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property string $remark 备注
 * @property null|Tenant $tenant
 * @property Collection<int, User>|User[] $members
 */
class TenantProject extends Model
{
    use HasWorkspaceScope;

    protected ?string $table = 'tenant_project';

    protected array $fillable = [
        'id',
        'tenant_id',
        'name',
        'is_default',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'remark',
    ];

    protected array $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'is_default' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deleted(Deleted $event): void
    {
        $this->members()->detach();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_project_user', 'project_id', 'user_id')
            ->withPivot(['tenant_id', 'created_by', 'updated_by'])
            ->withTimestamps();
    }
}
