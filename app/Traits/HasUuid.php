<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $keyType
 * @property bool $incrementing
 */
trait HasUuid
{

    /**
     * Boot the Uuid trait for the model.
     */
    public static function bootHasUuid()
    {
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), \Ramsey\Uuid\Uuid::uuid4()->toString());
        });
    }

    /**
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

}
