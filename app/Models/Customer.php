<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'plan_type', 'lifetime_value'];

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function jobs()
    {
        return $this->hasMany(CustomerJob::class);
    }
}