<?php

namespace App\Extensions;

trait HasCreatedUser {

    protected $foreignNameCreatedUser = 'created_uid';
    
    public function created_user()
    {
        return $this->belongsTo(User::class);
    }
    
    static function bootHasCreatedUser()
    {
        self::creating(function ($model) { 
            if (!$user = auth()->user()) abort(401, "The user authorization required");
            $model->{$model->foreignNameCreatedUser} = $user->id;
        });
    }
}
    