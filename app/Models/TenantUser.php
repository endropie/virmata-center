<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantUser extends Model
{
    use HasFactory, CentralConnection;

    public $timestamps = false;    

    protected $fillable = ['option'];

    protected $casts = [
        "option" => "array",
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    static public function booted()
    {
        static::creating(function (self $model) {
            if (!$model->option) {
                $model->setAttribute('option', ['level' => 'operator']);
            }
        });
    }
}
