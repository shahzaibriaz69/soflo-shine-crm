<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Foreign Key Checks ko temporarly OFF karein
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Customer::truncate();

        // 2. Customers insert karein
        Customer::create([
            'ghl_contact_id' => 'ghl_cnt_101',
            'name' => 'John Doe (Dummy)',
            'email' => 'john.doe@example.com',
            'phone' => '+1111111111',
        ]);

        Customer::create([
            'ghl_contact_id' => 'ghl_cnt_102',
            'name' => 'Sarah Connor (Dummy)',
            'email' => 'sarah.c@skynet.net',
            'phone' => '+2222222222',
        ]);

        // 3. Foreign Key Checks ko wapas ON karein
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}