<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GhlWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['type'] ?? $payload['event'] ?? 'unknown_event';
        $providerEventId = $payload['eventId'] ?? $payload['id'] ?? (string) Str::uuid();

        // 1. Log or ignore duplicate event for Idempotency
        $exists = DB::table('webhook_events')->where('provider_event_id', $providerEventId)->exists();
        
        if (!$exists) {
            DB::table('webhook_events')->insert([
                'id' => (string) Str::uuid(),
                'provider_event_id' => $providerEventId,
                'event_type' => $eventType,
                'received_time' => now(),
                'processing_status' => 'processing',
                'redacted_payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            // 2. Route payload based on GHL Event Type
            match (true) {
                str_contains(strtolower($eventType), 'contact') => $this->syncContact($payload),
                str_contains(strtolower($eventType), 'opportunity') => $this->syncOpportunity($payload),
                str_contains(strtolower($eventType), 'appointment') => $this->syncAppointment($payload),
                default => null,
            };

            // Update webhook event status to processed
            DB::table('webhook_events')
                ->where('provider_event_id', $providerEventId)
                ->update(['processing_status' => 'completed', 'updated_at' => now()]);

            return response()->json(['status' => 'success', 'message' => 'Webhook synchronized successfully.']);

        } catch (\Exception $e) {
            // Log failure status
            DB::table('webhook_events')
                ->where('provider_event_id', $providerEventId)
                ->update(['processing_status' => 'failed', 'updated_at' => now()]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    protected function syncContact(array $payload)
    {
        $ghlContactId = $payload['contactId'] ?? $payload['id'] ?? null;
        if (!$ghlContactId) return;

        DB::table('contacts')->updateOrInsert(
            ['ghl_id' => $ghlContactId], // assuming ghl_id column exists or map accordingly
            [
                'id' => (string) Str::uuid(),
                'first_name' => $payload['firstName'] ?? $payload['first_name'] ?? 'Unknown',
                'last_name' => $payload['lastName'] ?? $payload['last_name'] ?? '',
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function syncOpportunity(array $payload)
    {
        $ghlOpportunityId = $payload['opportunityId'] ?? $payload['id'] ?? null;
        if (!$ghlOpportunityId) return;

        DB::table('opportunities')->updateOrInsert(
            ['ghl_opportunity_id' => $ghlOpportunityId],
            [
                'id' => (string) Str::uuid(),
                'title' => $payload['name'] ?? $payload['opportunityName'] ?? 'New Opportunity',
                'monetary_value' => $payload['monetaryValue'] ?? $payload['value'] ?? 0.00,
                'pipeline_id' => $payload['pipelineId'] ?? null,
                'stage_id' => $payload['pipelineStageId'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function syncAppointment(array $payload)
    {
        $ghlAppointmentId = $payload['appointmentId'] ?? $payload['id'] ?? null;
        if (!$ghlAppointmentId) return;

        DB::table('appointments')->updateOrInsert(
            ['ghl_appointment_id' => $ghlAppointmentId],
            [
                'id' => (string) Str::uuid(),
                'calendar_id' => $payload['calendarId'] ?? null,
                'start_time' => isset($payload['startTime']) ? Carbon::parse($payload['startTime']) : now(),
                'end_time' => isset($payload['endTime']) ? Carbon::parse($payload['endTime']) : now()->addHour(),
                'status' => $payload['status'] ?? 'confirmed',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}