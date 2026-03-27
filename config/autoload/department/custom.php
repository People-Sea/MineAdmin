<?php

declare(strict_types=1);
use App\Library\DataPermission\Context;
use App\Library\DataPermission\ScopeType;
use App\Model\DataPermission\Policy;
use App\Model\Permission\User;
use Hyperf\Database\Query\Builder;

return [
    'testction' => static function (Builder $builder, ScopeType $scopeType, Policy $policy, User $user) {
        // 只针对 id 为 2 的用户生效
        if ($user->id !== 2) {
            return;
        }
        // 获取当前上下文中的创建人字段名称
        $createdByColumn = Context::getCreatedByColumn();
        // 获取当前上下文中的部门字段名称
        $deptColumn = Context::getDeptColumn();
        switch ($scopeType) {
            // 隔离类型为根据创建人
            case ScopeType::CREATED_BY:
                // 创建人字段为当前用户
                $builder->where($createdByColumn, $user->id);
                break;
            case ScopeType::DEPT:
                // 部门字段为当前用户部门
                $builder->whereIn($deptColumn, $user->department()->get()->pluck('id'));
                break;
            case ScopeType::DEPT_CREATED_BY:
                // 部门字段为当前用户部门
                $builder->whereIn($deptColumn, $user->department()->get()->pluck('id'));
                // 创建人为当前用户
                $builder->where($createdByColumn, $user->id);
                break;
            case ScopeType::DEPT_OR_CREATED_BY:
                // 部门字段为当前用户部门
                $builder->whereIn($deptColumn, $user->department()->get()->pluck('id'));
                // 创建人为当前用户
                $builder->orWhere($createdByColumn, $user->id);
                break;
        }
    },
];
