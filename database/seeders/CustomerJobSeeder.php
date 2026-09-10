<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerJob; // Model class reference

class CustomerJobSeeder extends Seeder
{
    public function run(): void
    {
        $customer1 = Customer::find(1);
        $customer2 = Customer::find(2);

        if ($customer1) {
            CustomerJob::create([
                'customer_id'    => $customer1->id,
                'job_number'     => 'SOFLO-1001',
                'scheduled_at'   => now()->subDays(2),
                'status'         => 'completed',
                'labor_cost'     => 100.00,
                'materials_cost' => 50.00,
                'net_profit'     => 100.00,
            ]);
        }

        if ($customer2) {
            CustomerJob::create([
                'customer_id'    => $customer2->id,
                'job_number'     => 'SOFLO-1002',
                'scheduled_at'   => now()->addDay(),
                'status'         => 'in_progress',
                'labor_cost'     => 250.00,
                'materials_cost' => 150.00,
                'net_profit'     => 450.00,
            ]);
        }
    }
}