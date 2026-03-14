<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Skip if running in console (migrations, etc.) unless explicitly needed
        if (app()->runningInConsole()) {
            return;
        }

        // Use hasUser() to check if user is already resolved to avoid infinite recursion
        // especially when this scope is applied to the User model itself.
        if (Auth::hasUser()) {
            $builder->where($model->getTable() . '.cid', Auth::user()->cid);
        } elseif (session()->has('cid')) {
            // Fallback to session cid if available (e.g. during login process)
            $builder->where($model->getTable() . '.cid', session('cid'));
        }
    }
}
