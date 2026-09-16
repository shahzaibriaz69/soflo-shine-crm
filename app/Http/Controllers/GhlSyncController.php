<?php

namespace App\Http\Controllers;

use App\Services\GoHighLevelService;

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
}