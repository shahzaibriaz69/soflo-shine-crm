<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * We create realistic dummy customers to map jobs and dashboard stats.
     */
    public function run(): void
    {
        // 1. Create primary dummy customer (ID: 1)
        Customer::create([
            'ghl_contact_id' => 'ghl_cnt_101', // Local dummy format
            'name' => 'John Doe (Dummy)',
            'email' => 'john.doe@example.com',
            'phone' => '+1111111111',
        ]);

        // 2. Create secondary dummy customer (ID: 2)
        Customer::create([
            'ghl_contact_id' => 'ghl_cnt_102', // Another local dummy format
            'name' => 'Sarah Connor (Dummy)',
            'email' => 'sarah.c@skynet.net',
            'phone' => '+2222222222',
        ]);
    }
}