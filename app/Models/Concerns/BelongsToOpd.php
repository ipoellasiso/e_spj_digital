<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Membatasi query ke OPD milik user yang login.
 * - Superadmin melihat semua OPD.
 * - User non-superadmin tanpa opd_id TIDAK melihat data apa pun (bukan semua data).
 * - Tanpa user login (queue, artisan, test) scope tidak dipasang; kirim opd_id secara eksplisit.
 */
trait BelongsToOpd
{
    protected static function bootBelongsToOpd(): void
    {
        static::addGlobalScope('opd', function (Builder $query) {
            $user = auth()->user();
            if ($user && ! $user->isSuperadmin()) {
                $query->where($query->getModel()->getTable() . '.opd_id', $user->opd_id ?? 0);
            }
        });

        static::creating(function ($model) {
            $user = auth()->user();
            if (empty($model->opd_id) && $user && $user->opd_id) {
                $model->opd_id = $user->opd_id;
            }
        });
    }
}