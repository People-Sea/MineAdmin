<?php

declare(strict_types=1);

namespace App\Schema;

use App\Model\DataPermission\Policy;
use App\Model\Enums\DataPermission\PolicyType;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

/**
 * @see Policy
 */
#[Schema(title: 'PolicySchema')]
class PolicySchema implements \JsonSerializable
{
    #[Property(property: 'policy_type', title: '策略类型', type: 'string')]
    protected PolicyType $policyType;

    #[Property(property: 'is_default', title: '是否默认策略', type: 'bool')]
    protected bool $isDefault;

    #[Property(property: 'value', title: '策略值', type: 'object')]
    protected array $value;

    public function __construct(protected Policy $model) {}

    public function jsonSerialize(): mixed
    {
        return [
            'policy_type' => $this->policyType->value,
            'is_default' => $this->isDefault,
            'value' => $this->value,
        ];
    }
}
