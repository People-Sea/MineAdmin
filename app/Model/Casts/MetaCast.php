<?php

declare(strict_types=1);

namespace App\Model\Casts;

use App\Model\Permission\Meta;
use Hyperf\Codec\Json;
use Hyperf\Contract\CastsAttributes;
use Hyperf\DbConnection\Model\Model;

class MetaCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): Meta
    {
        return new Meta(empty($value) ? [] : Json::decode($value));
    }

    /**
     * @param Meta $value
     * @param Model $model
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return Json::encode($value);
    }
}
