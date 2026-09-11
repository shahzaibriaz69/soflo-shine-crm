<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'sales');

        if (! in_array($activeTab, ['sales', 'recurring'], true)) {
            $activeTab = 'sales';
        }

        // Agar pipelines table empty hai, toh initial dummy pipelines aur stages create kar dein
        if (Pipeline::count() === 0) {
            DB::transaction(function () {
                // 1. Sales Pipeline
                $salesPipeline = Pipeline::create([
                    'name' => 'Sales Pipeline',
                    'type' => 'sales'
                ]);

                $salesStages = ['New Lead', 'Contacted', 'Quoted', 'Won', 'Lost'];
                foreach ($salesStages as $index => $stageName) {
                    $stage = Stage::create([
                        'pipeline_id' => $salesPipeline->id,
                        'name' => $stageName,
                        'order' => $index + 1
                    ]);

                    // Har stage mein testing ke liye aik dummy opportunity dal dein
                    if ($stageName === 'New Lead') {
                        Opportunity::create([
                            'pipeline_id' => $salesPipeline->id,
                            'stage_id' => $stage->id,
                            'name' => '2021 Tesla Model Y — Ashley Nguyen',
                            'description' => 'Interior + Exterior Full Detail · (305) 555-0147',
                            'value' => 402.00,
                            'stage_location' => Str::slug($stageName, '_'),
                            'status' => 'open',
                            'time_in_stage' => '12m in stage'
                        ]);
                    }
                }

                // 2. Recurring Pipeline
                $recurringPipeline = Pipeline::create([
                    'name' => 'Recurring Maintenance',
                    'type' => 'recurring'
                ]);

                $recurringStages = ['Active Maintenance', 'Due for Service', 'Completed'];
                foreach ($recurringStages as $index => $stageName) {
                    Stage::create([
                        'pipeline_id' => $recurringPipeline->id,
                        'name' => $stageName,
                        'order' => $index + 1
                    ]);
                }
            });
        }

        $salesPipelines = Pipeline::where('type', 'sales')->with('stages.opportunities')->get();
        $recurringPipelines = Pipeline::where('type', 'recurring')->with('stages.opportunities')->get();

        return view('pipeline', compact('salesPipelines', 'recurringPipelines', 'activeTab'));
    }

    public function updateStage(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:opportunities,id',
                'stage_id' => 'required|exists:stages,id',
            ]);

            $opportunity = Opportunity::findOrFail($validated['id']);
            $targetStage = Stage::findOrFail($validated['stage_id']);

            DB::transaction(function () use ($opportunity, $targetStage): void {
                $stageKey = Str::slug($targetStage->name, '_');

                $opportunity->update([
                    'stage_id' => $targetStage->id,
                    'pipeline_id' => $targetStage->pipeline_id,
                    'stage_location' => $stageKey, 
                    'status' => $stageKey === 'won' ? 'won' : 'open',
                    'time_in_stage' => '0m in stage',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Stage updated successfully in database!',
                'received_id' => $opportunity->id,
                'new_stage_id' => $targetStage->id,
                'stage_location' => Str::slug($targetStage->name, '_'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}