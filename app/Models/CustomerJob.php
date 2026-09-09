<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerJob extends Model
{
     protected $fillable = [
        'job_number', 'customer_id', 'quote_id', 
        'scheduled_at', 'status', 'labor_cost', 
        'materials_cost', 'net_profit'
    ];
}
