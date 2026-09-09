<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'quote_number', 'customer_id', 'package_id', 
        'vehicle_size_multiplier', 'condition_multiplier', 
        'total_amount', 'status', 'accepted_at'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function job()
    {
        return $this->hasOne(CustomerJob::class);
    }
}
