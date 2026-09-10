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
                    'stage_location' => $stageKey, // Updated column name for phpMyAdmin
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