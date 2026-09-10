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

        // Sales aur Recurring pipelines ko unke stages aur opportunities ke sath fetch karein
        $salesPipelines = Pipeline::where('type', 'sales')->with('stages.opportunities')->get();
        $recurringPipelines = Pipeline::where('type', 'recurring')->with('stages.opportunities')->get();

        return view('pipeline', compact('salesPipelines', 'recurringPipelines', 'activeTab'));
    }

    public function updateStage(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:opportunities,id',
            'stage_id' => 'required|exists:stages,id',
        ]);

        $opportunity = Opportunity::findOrFail($validated['id']);
        $targetStage = Stage::findOrFail($validated['stage_id']);

        abort_unless(
            $opportunity->pipelineStage?->pipeline_id === $targetStage->pipeline_id,
            422,
            'An opportunity can only move within its current pipeline.'
        );

        DB::transaction(function () use ($opportunity, $targetStage): void {
            $stageKey = Str::slug($targetStage->name, '_');

            $opportunity->update([
                'stage_id' => $targetStage->id,
                'pipeline_id' => $targetStage->pipeline_id,
                'stage' => $stageKey,
                'status' => $stageKey === 'won' ? 'won' : 'open',
                'time_in_stage' => '0m in stage',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Stage updated successfully in database!',
            'received_id' => $opportunity->id,
            'new_stage_id' => $targetStage->id,
            'stage' => Str::slug($targetStage->name, '_'),
        ]);
    }
}
