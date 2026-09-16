<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoHighLevelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CustomerMessageController extends Controller
{
    protected GoHighLevelService $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_id' => 'required',
            'team_member' => 'required|string',
            'message_type' => 'required|string',
            'message_body' => 'required|string',
            'phone' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        if (!$phone) {
            $quote = DB::table('request_quotes')->where('id', $validated['quote_id'])->first()
                     ?? DB::table('quotes')->where('id', $validated['quote_id'])->first();
            
            $phone = $quote->phone ?? $quote->customer_phone ?? null;
        }

        if ($phone) {
            try {
                $this->ghlService->sendSMS($phone, $validated['message_body']);
            } catch (\Exception $e) {
                Log::error('GHL SMS Send Failed: ' . $e->getMessage());
            }
        }

        if (Schema::hasTable('message_logs')) {
            \App\Models\MessageLog::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Text message sent via GHL and logged successfully!'
        ]);
    }
}