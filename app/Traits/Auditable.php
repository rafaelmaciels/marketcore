<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditService::log(
                action: 'create',
                description: 'Criação de ' . class_basename($model) . ' #' . $model->getKey(),
                auditable: $model,
                newValues: $model->getAttributes()
            );
        });

        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = [];
            foreach (array_keys($dirty) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            // Remove campos sensíveis
            unset($old['password'], $dirty['password'], $old['remember_token'], $dirty['remember_token']);

            AuditService::log(
                action: 'update',
                description: 'Atualização de ' . class_basename($model) . ' #' . $model->getKey(),
                auditable: $model,
                oldValues: $old,
                newValues: $dirty
            );
        });

        static::deleted(function (Model $model) {
            AuditService::log(
                action: 'delete',
                description: 'Exclusão de ' . class_basename($model) . ' #' . $model->getKey(),
                auditable: $model,
                oldValues: $model->getAttributes()
            );
        });
    }
}
