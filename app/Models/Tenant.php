<?php

namespace App\Models;

use Endropie\LumenRestApi\Concerns\HasFilterable;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFilterable;

    public function invites()
    {
        return $this->hasMany(\App\Models\TenantInvite::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, \App\Models\TenantUser::class);
    }

    public function setUserAccess(User $user, $option = [])
    {
        $this->users()->save($user, ['option' => json_encode($option)]);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id', 'name', 'address', 'tenant_type_id', 'owner_uid',
        ];
    }

    static function booted()
    {
        static::creating(function ($model) { 
            $model->owner_uid = auth()->user()?->id;

            return $model;
        });

        static::created(function ($model) { 
            $model->domains()->create(['domain' => $model->id .'.'. env('APP_DOMAIN', 'localhost')]);
        });
    }
}