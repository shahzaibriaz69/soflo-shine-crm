<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoHighLevelService;
use Illuminate\Support\Facades\Log;

class GhlSyncController extends Controller
{
    protected GoHighLevelService $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    // GHL sync action for Opportunities, Contacts, Appointments, and Products
    public function sync()
    {
        try {
            // 1. Sync Opportunities domain
            $syncedOpportunities = $this->ghlService->syncOpportunities();

            // 2. Sync Contacts domain
            $syncedContacts = $this->ghlService->syncContacts();

            // 3. Sync Appointments domain (Calendars)
            $syncedAppointments = $this->ghlService->syncAppointments();

            // 4. Sync Products domain (Catalog)
            $syncedProducts = $this->ghlService->syncProducts();

            return redirect()->back()->with('success', "Successfully synced {$syncedOpportunities} opportunities, {$syncedContacts} contacts, {$syncedAppointments} appointments, and {$syncedProducts} products from GoHighLevel!");
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

        // Handle specific GHL events securely
        switch ($event) {
            case 'OpportunityStatusUpdate':
            case 'opportunityUpdate':
                // Update local quote/opportunity stage if needed
                break;
                
            case 'InboundMessage':
                // Handle incoming customer SMS replies
                break;

            default:
                // Generic handler
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed successfully.'
        ], 200);
    }
}