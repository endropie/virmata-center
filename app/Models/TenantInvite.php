<?php

namespace App\Models;

use App\Extensions\HasCreatedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantInvite extends Model
{
    use HasFactory, CentralConnection, HasCreatedUser, SoftDeletes;

    protected $createdPlainToken = null;

    protected $fillable = ['context', 'level'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeWhereToken($query, $plainToken)
    {
        return $query->where('token', hash('sha256', $plainToken));
    }

    public function scopeAuthorized($query)
    {
        $user = auth()->user();
        return $query->whereIn('context', [$user->email]);
    }

    public function scopeInviting($query)
    {
        return $query->whereNull('confirmed_at');
    }

    public function setConfirmed($confirm = 'accepted' | 'rejected', $option = [])
    {
        if ($confirm == 'accepted')
        {
            $this->tenant->setUserAccess(auth()->user(), $option);
        }

        $this->setRawAttributes([
            'confirm' => $confirm,
            'confirmed_at' => app('db')->raw('CURRENT_TIMESTAMP'),
        ]);

        $this->save();
    }

    public function setCreatedPlainToken($token)
    {
        $this->createdPlainToken = $token;
    }

    public function getCreatedPlainToken()
    {
        return $this->createdPlainToken;
    }

    static public function booted()
    {
        static::creating(function (self $model) {

            $model->token = hash('sha256', $plainToken = stringable()->random(40));
            if ($plainToken && env('APP_ENV') === "local")
            {
                $model->setCreatedPlainToken($plainToken);
            }
        });
    }
}
