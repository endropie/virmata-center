<?php

namespace App\Models;

use Endropie\LumenRestApi\Concerns\HasFilterable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFilterable, SoftDeletes;

    public function invites()
    {
        return $this->hasMany(\App\Models\TenantInvite::class);
    }

    public function tenant_users()
    {
        return $this->hasMany(\App\Models\TenantUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, \App\Models\TenantUser::class);
    }

    public function isOwner() :? bool
    {
        if (!$user = auth()->user()) return null;

        if ($this->owner_uid === $user->id) true;

        $access = $this->tenant_users()->where('user_id', $user->id)->first();

        return (bool) ($access?->option['level'] == 'administrator');
    }

    public function asLevel() :? string
    {
        if (!$user = auth()->user()) return null;

        if ($this->owner_uid === $user->id) return 'administrator';

        $access = $this->tenant_users()->where('user_id', $user->id)->first();

        return $access->option['level'] ?? null;
    }

    public function setUserAccess(User $user, $option = [])
    {
        $this->users()->save($user, ['option' => json_encode($option)]);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id', 'name', 'address', 'tenant_type_id', 'owner_uid',
            'created_at', 'updated_at', 'deleted_at',
        ];
    }

    public function scopeAuthorized($query)
    {

        return $query->where(function($q) {
            $id = auth()->user()?->id ?? null;
            return $q->where('owner_uid', $id)
                     ->orWhereHas('tenant_users', fn($q) => $q->where('user_id', $id));
        });
    }

    static function booted()
    {
        static::creating(function ($model) {
            if (!$model->cluster) $model->cluster = config('tenancy.app.cluster.defaults');

            $model->owner_uid = auth()->user()?->id;
        });

        static::created(function ($model) {
            $model->domains()->create(['domain' => $model->id .'.'. env('APP_DOMAIN', 'localhost')]);
        });
    }
}
