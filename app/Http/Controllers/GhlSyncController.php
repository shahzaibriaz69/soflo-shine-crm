<?php

namespace App\Http\Controllers;

use App\Services\GoHighLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GhlSyncController extends Controller
{
    protected GoHighLevelService $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    // GHL sync action for Opportunities, Contacts, Team Users, Appointments, and Products
    public function sync()
    {
        try {
            $synced = $this->ghlService->syncAll();

            if ($synced['errors'] !== []) {
                return redirect()->back()->with(
                    'error',
                    'GHL sync is incomplete. Saved '.$synced['opportunities'].' opportunities, '.$synced['contacts'].' contacts, '.$synced['users'].' team members, '.$synced['appointments'].' appointments, and '.$synced['products'].' products. Check the GHL token permissions for: '.implode(', ', array_keys($synced['errors'])).'.',
                );
            }

            return redirect()->back()->with('success', "GHL sync complete: {$synced['opportunities']} opportunities, {$synced['contacts']} contacts, {$synced['users']} team members, {$synced['appointments']} appointments, and {$synced['products']} products saved to the database.");
        } catch (\Exception $e) {
            Log::error('GHL sync failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'GHL sync failed. Check your token, location ID, and GHL permissions, then try again.');
        }
    }

    /**
     * Handle incoming webhooks from GoHighLevel
     */
    public function handleWebhook(Request $request)
    {
        // Log the incoming webhook payload for auditing and debugging
        Log::info('GHL Webhook Received: ', $request->all());

        $event = $request->input('type') ?? $request->input('event');
        $payload = $request->all();

        // Handle specific GHL events securely and update local DB tables
        switch ($event) {
            case 'OpportunityStatusUpdate':
            case 'opportunityUpdate':
            case 'OpportunityCreate':
                $oppId = $payload['id'] ?? $payload['opportunityId'] ?? null;
                if ($oppId && Schema::hasTable('opportunities')) {
                    DB::table('opportunities')->updateOrInsert(
                        ['ghl_opportunity_id' => $oppId],
                        [
                            'name' => $payload['name'] ?? 'Updated Opportunity',
                            'stage' => $payload['stageId'] ?? $payload['stage'] ?? 'new',
                            'status' => $payload['status'] ?? 'open',
                            'value' => $payload['monetaryValue'] ?? ($payload['value'] ?? 0),
                            'updated_at' => now(),
                        ]
                    );
                }
                break;

            case 'ContactCreate':
            case 'ContactUpdate':
                $contact = $payload['contact'] ?? $payload;
                $email = $contact['email'] ?? null;
                if ($email && Schema::hasTable('contacts')) {
                    DB::table('contacts')->updateOrInsert(
                        ['email' => $email],
                        [
                            'name' => trim(($contact['firstName'] ?? '').' '.($contact['lastName'] ?? '')),
                            'phone' => $contact['phone'] ?? null,
                            'updated_at' => now(),
                        ]
                    );
                }
                break;

            case 'InboundMessage':
                Log::info('GHL Inbound Message Received: ', $payload);
                break;

            default:
                Log::info('Unhandled GHL Webhook Event: '.$event);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed successfully.',
        ], 200);
    }

    public function syncStaffToGHL($staffMember)
    {
        $apiKey = config('services.ghl.api_key');
        $locationId = config('services.ghl.location_id');

        if (! $apiKey || ! $locationId) {
            return response()->json(['success' => false, 'error' => 'GHL API Key or Location ID is missing in configuration.']);
        }

        $response = Http::withToken($apiKey)
            ->withHeaders([
                'Version' => '2021-07-28',
                'Content-Type' => 'application/json',
            ])
            ->post('https://services.leadconnectorhq.com/users/', [
                'firstName' => $staffMember->first_name ?? explode(' ', $staffMember->name)[0] ?? '',
                'lastName' => $staffMember->last_name ?? explode(' ', $staffMember->name)[1] ?? '',
                'email' => $staffMember->email,
                'phone' => $staffMember->phone ?? null,
                'role' => $staffMember->role ?? 'account-user',
                'locationId' => $locationId,
            ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'data' => $response->json()]);
        }

        Log::error('GHL Sync Staff Error: '.$response->body());

        return response()->json(['success' => false, 'error' => $response->body()]);
    }
}
