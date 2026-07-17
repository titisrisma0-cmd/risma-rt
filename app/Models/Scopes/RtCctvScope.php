<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class RtCctvScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isAdminRt()) {
            return;
        }

        if ($model instanceof \App\Models\CctvCamera) {
            $builder->where($model->qualifyColumn('rt_id'), $user->managedRtId());
        }

        if ($model instanceof \App\Models\CctvRecording) {
            $builder->whereHas('camera', fn (Builder $query) => $query->where('rt_id', $user->managedRtId()));
        }
    }
}
