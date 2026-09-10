<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestQuote extends Model
{
    use HasFactory;

    protected $guarded = []; // Yeh line saari fields ko mass-assignment ki permission de deti hai

    protected $casts = [
        'photos' => 'array',
    ];
}