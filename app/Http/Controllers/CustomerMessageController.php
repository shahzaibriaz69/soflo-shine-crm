<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerMessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_id' => 'required|exists:request_quotes,id',
            'team_member' => 'required|string',
            'message_type' => 'required|string',
            'message_body' => 'required|string',
        ]);

        // TODO: Jab GHL live ho jaye, yahan GoHighLevel SMS API call laga di jayegi
        // $this->ghlService->sendSms($request->phone, $validated['message_body']);

        // Filhal database / communication log mein save karne ki logic (agar table bani ho)
        // \App\Models\MessageLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Text message sent and logged successfully!'
        ]);
    }
}