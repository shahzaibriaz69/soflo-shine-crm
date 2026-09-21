<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    protected $table = "contacts";
    protected $fillable = ['ghl_contact_id', 'first_name', 'last_name', 'email', 'phone', 'location_id'];
    public function contacts()
    {
        return $this->hasMany(Customer::class, 'ghl_contact_id', 'ghl_contact_id');
    }
}
