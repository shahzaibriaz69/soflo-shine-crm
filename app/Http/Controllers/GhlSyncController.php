<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\GoHighLevelService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            // 1. Sync Opportunities domain
            $syncedOpportunities = $this->ghlService->syncOpportunities();

            // 2. Sync Contacts domain
            $syncedContacts = $this->ghlService->syncContacts();

            // 3. Sync Team Members / Users domain
            $syncedUsers = $this->ghlService->syncUsers();

            // 4. Sync Appointments domain (Calendars)
            $syncedAppointments = $this->ghlService->syncAppointments();

            // 5. Sync Products domain (Catalog)
            $syncedProducts = $this->ghlService->syncProducts();

            return redirect()->back()->with('success', "Successfully synced {$syncedOpportunities} opportunities, {$syncedContacts} contacts, {$syncedUsers} team members, {$syncedAppointments} appointments, and {$syncedProducts} products from GoHighLevel!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sync failed: ' . $e->getMessage());
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
                            'name' => trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')),
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
                Log::info('Unhandled GHL Webhook Event: ' . $event);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed successfully.'
        ], 200);
    }

    public function syncStaffToGHL($staffMember)
    {
        $apiKey = config('services.ghl.api_key');
        $locationId = config('services.ghl.location_id');

        if (!$apiKey || !$locationId) {
            return response()->json(['success' => false, 'error' => 'GHL API Key or Location ID is missing in configuration.']);
        }

        $response = Http::withToken($apiKey)
            ->withHeaders([
                'Version' => '2021-07-28',
                'Content-Type' => 'application/json'
            ])
            ->post('https://services.leadconnectorhq.com/users/', [
                'firstName' => $staffMember->first_name ?? explode(' ', $staffMember->name)[0] ?? '',
                'lastName' => $staffMember->last_name ?? explode(' ', $staffMember->name)[1] ?? '',
                'email' => $staffMember->email,
                'phone' => $staffMember->phone ?? null,
                'role' => $staffMember->role ?? 'account-user',
                'locationId' => $locationId
            ]);

        if ($response->successful()) {
            return response()->json(['success' => true, 'data' => $response->json()]);
        }

        Log::error('GHL Sync Staff Error: ' . $response->body());
        return response()->json(['success' => false, 'error' => $response->body()]);
    }
}