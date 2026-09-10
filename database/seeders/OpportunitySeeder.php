<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        // Foreign key constraints ki wajah se pehle truncate ya delete karein
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('opportunities')->truncate();
        DB::table('stages')->truncate();
        DB::table('pipelines')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Pipelines
        $salesPipelineId = DB::table('pipelines')->insertGetId([
            'name' => 'Sales',
            'type' => 'sales',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recurringPipelineId = DB::table('pipelines')->insertGetId([
            'name' => 'Recurring Plans',
            'type' => 'recurring',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Sales Stages
        $salesStages = [
            'new' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'New', 'order' => 1, 'created_at' => now(), 'updated_at' => now()]),
            'contacted' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'Contacted', 'order' => 2, 'created_at' => now(), 'updated_at' => now()]),
            'quote_sent' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'Quote Sent', 'order' => 3, 'created_at' => now(), 'updated_at' => now()]),
            'follow_up' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'Follow Up', 'order' => 4, 'created_at' => now(), 'updated_at' => now()]),
            'scheduled' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'Scheduled', 'order' => 5, 'created_at' => now(), 'updated_at' => now()]),
            'won' => DB::table('stages')->insertGetId(['pipeline_id' => $salesPipelineId, 'name' => 'Won', 'order' => 6, 'created_at' => now(), 'updated_at' => now()]),
        ];

        // 3. Create Recurring Plans Stages
        $recurringStages = [
            'plan_offered' => DB::table('stages')->insertGetId(['pipeline_id' => $recurringPipelineId, 'name' => 'Plan Offered', 'order' => 1, 'created_at' => now(), 'updated_at' => now()]),
            'negotiating' => DB::table('stages')->insertGetId(['pipeline_id' => $recurringPipelineId, 'name' => 'Negotiating', 'order' => 2, 'created_at' => now(), 'updated_at' => now()]),
            'active_member' => DB::table('stages')->insertGetId(['pipeline_id' => $recurringPipelineId, 'name' => 'Active Member', 'order' => 3, 'created_at' => now(), 'updated_at' => now()]),
            'renewal_due' => DB::table('stages')->insertGetId(['pipeline_id' => $recurringPipelineId, 'name' => 'Renewal Due', 'order' => 4, 'created_at' => now(), 'updated_at' => now()]),
        ];

        // 4. Insert Opportunities for Sales & Recurring Pipelines
        DB::table('opportunities')->insert([
            // Sales Pipeline Opportunities
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['new'],
                'ghl_opportunity_id' => 'ghl_opt_1001',
                'name' => 'Ashley Nguyen',
                'description' => 'Full detail – Tesla Model Y · Instagram DM',
                'value' => 0.00,
                'time_in_stage' => '12m in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['new'],
                'ghl_opportunity_id' => 'ghl_opt_1002',
                'name' => 'Devon Pierce',
                'description' => 'Exterior + ceramic – F-150 · web form',
                'value' => 0.00,
                'time_in_stage' => '1h in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['contacted'],
                'ghl_opportunity_id' => 'ghl_opt_1003',
                'name' => 'Camila Ortiz',
                'description' => 'Interior detail – 2023 Civic',
                'value' => 185.00,
                'time_in_stage' => '3h in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['quote_sent'],
                'ghl_opportunity_id' => 'ghl_opt_1004',
                'name' => 'James Whitfield',
                'description' => 'Full detail + ceramic – BMW X5',
                'value' => 1494.00,
                'time_in_stage' => '3d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['quote_sent'],
                'ghl_opportunity_id' => 'ghl_opt_1005',
                'name' => 'Harbor Point Auto Group',
                'description' => 'Lot detail – 8 units',
                'value' => 1240.00,
                'time_in_stage' => '5d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['follow_up'],
                'ghl_opportunity_id' => 'ghl_opt_1006',
                'name' => 'Marcus Webb',
                'description' => 'Express wash – Tahoe · sent 1d ago',
                'value' => 85.00,
                'time_in_stage' => '1d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['scheduled'],
                'ghl_opportunity_id' => 'ghl_opt_1007',
                'name' => 'Coastal Fleet Rentals',
                'description' => 'Quarterly fleet wash – 8 vans',
                'value' => 690.00,
                'time_in_stage' => '1d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $salesPipelineId,
                'stage_id' => $salesStages['won'],
                'ghl_opportunity_id' => 'ghl_opt_1008',
                'name' => 'Maria Delgado',
                'description' => 'Monthly maintenance plan – signed',
                'value' => 465.00,
                'time_in_stage' => '8d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Recurring Plans Pipeline Opportunities (Aapki image ke mutabiq)
            [
                'pipeline_id' => $recurringPipelineId,
                'stage_id' => $recurringStages['active_member'],
                'ghl_opportunity_id' => 'ghl_opt_rec_1',
                'name' => 'Maria Delgado',
                'description' => 'Monthly maintenance – $145/mo',
                'value' => 1740.00,
                'time_in_stage' => '32d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $recurringPipelineId,
                'stage_id' => $recurringStages['active_member'],
                'ghl_opportunity_id' => 'ghl_opt_rec_2',
                'name' => 'Coastal Fleet Rentals',
                'description' => 'Quarterly fleet – $690/qtr',
                'value' => 2760.00,
                'time_in_stage' => '96d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_id' => $recurringPipelineId,
                'stage_id' => $recurringStages['renewal_due'],
                'ghl_opportunity_id' => 'ghl_opt_rec_3',
                'name' => 'Sunrise Rideshare Co.',
                'description' => 'Quarterly – $560/qtr',
                'value' => 2240.00,
                'time_in_stage' => '12d in stage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
