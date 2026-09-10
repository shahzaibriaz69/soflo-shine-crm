<?php

namespace App\Http\Controllers;

use App\Services\GoHighLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    protected $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    public function index()
    {
        $opportunities = $this->ghlService->getOpportunities();

        $stages = [
            'new'        => ['title' => 'NEW'],
            'contacted'  => ['title' => 'CONTACTED'],
            'quote_sent' => ['title' => 'QUOTE SENT'],
            'follow_up'  => ['title' => 'FOLLOW-UP'],
            'scheduled'  => ['title' => 'SCHEDULED'],
            'won'        => ['title' => 'WON'],
        ];

        return view('pipeline', compact('opportunities', 'stages'));
    }

    // Stage Update API Endpoint
    public function updateStage(Request $request)
    {
        $request->validate([
            'id'    => 'required',
            'stage' => 'required|string',
        ]);

        // Direct primary ID se database row update karein
        $updated = DB::table('opportunities')
            ->where('id', $request->id)
            ->update([
                'stage'      => $request->stage,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => $updated ? true : false,
            'message' => $updated ? 'Stage updated successfully in database!' : 'Record not found!',
            'received_id' => $request->id,
            'new_stage' => $request->stage
        ]);
    }
}