<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        // Table clear karein taake duplicates na banein
        DB::table('opportunities')->truncate();

        DB::table('opportunities')->insert([
            ['ghl_opportunity_id' => 'ghl_opt_1001', 'name' => 'Ashley Nguyen', 'description' => 'Full detail – Tesla Model Y · Instagram DM', 'stage' => 'new', 'value' => 0.00, 'time_in_stage' => '12m in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1002', 'name' => 'Devon Pierce', 'description' => 'Exterior + ceramic – F-150 · web form', 'stage' => 'new', 'value' => 0.00, 'time_in_stage' => '1h in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1003', 'name' => 'Camila Ortiz', 'description' => 'Interior detail – 2023 Civic', 'stage' => 'contacted', 'value' => 185.00, 'time_in_stage' => '3h in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1004', 'name' => 'James Whitfield', 'description' => 'Full detail + ceramic – BMW X5', 'stage' => 'quote_sent', 'value' => 1494.00, 'time_in_stage' => '3d in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1005', 'name' => 'Harbor Point Auto Group', 'description' => 'Lot detail – 8 units', 'stage' => 'quote_sent', 'value' => 1240.00, 'time_in_stage' => '5d in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1006', 'name' => 'Marcus Webb', 'description' => 'Express wash – Tahoe · sent 1d ago', 'stage' => 'follow_up', 'value' => 85.00, 'time_in_stage' => '1d in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1007', 'name' => 'Coastal Fleet Rentals', 'description' => 'Quarterly fleet wash – 8 vans', 'stage' => 'scheduled', 'value' => 690.00, 'time_in_stage' => '1d in stage', 'created_at' => now(), 'updated_at' => now()],
            ['ghl_opportunity_id' => 'ghl_opt_1008', 'name' => 'Maria Delgado', 'description' => 'Monthly maintenance plan – signed', 'stage' => 'won', 'value' => 465.00, 'time_in_stage' => '8d in stage', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}