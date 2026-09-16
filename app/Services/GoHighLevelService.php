<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GoHighLevelService
{
    protected string $baseUrl = 'https://services.leadconnectorhq.com';

    /**
     * Fetch Live Opportunities or fallback to Local Database Mock Data
     */
    public function getOpportunities(?string $locationId = null, ?string $accessToken = null): array
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key'); 

        if (!$locationId || !$accessToken) {
            return $this->getOpportunitiesByType('sales');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/opportunities/search", [
                'location_id' => $locationId,
                'limit' => 20,
            ]);

            if ($response->successful()) {
                return $response->json('opportunities') ?? [];
            }

            Log::error('GHL API Response Error: ' . $response->body());
            return $this->getOpportunitiesByType('sales');

        } catch (\Exception $e) {
            Log::error('GHL API Exception: ' . $e->getMessage());
            return $this->getOpportunitiesByType('sales');
        }
    }

    /**
     * Fetch live opportunities from GHL and sync them to the local database
     */
    public function syncOpportunities(): int
    {
        $ghlOpportunities = $this->getOpportunities();

        if (empty($ghlOpportunities)) {
            return 0;
        }

        $syncedCount = 0;

        foreach ($ghlOpportunities as $opp) {
            $ghlId = $opp['id'] ?? $opp['opportunityId'] ?? null;
            
            if (!$ghlId) continue;

            DB::table('opportunities')->updateOrInsert(
                ['ghl_opportunity_id' => $ghlId], 
                [
                    'name' => $opp['name'] ?? 'Unnamed Opportunity',
                    'description' => $opp['note'] ?? '',
                    'stage' => $opp['stageId'] ?? $opp['stage'] ?? 'new',
                    'status' => $opp['status'] ?? 'open',
                    'value' => (float) ($opp['monetaryValue'] ?? $opp['value'] ?? 0),
                    'pipeline_type' => 'sales',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $syncedCount++;
        }

        return $syncedCount;
    }

    /**
     * Fetch live contacts from GHL API v2 and sync them to the local database
     */
    public function syncContacts(?string $locationId = null, ?string $accessToken = null): int
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/contacts/", [
                'locationId' => $locationId,
                'limit' => 20,
            ]);

            if ($response->successful()) {
                $contacts = $response->json('contacts') ?? [];
                $syncedCount = 0;

                foreach ($contacts as $contact) {
                    $ghlContactId = $contact['id'] ?? null;
                    if (!$ghlContactId) continue;

                    DB::table('contacts')->updateOrInsert(
                        ['ghl_contact_id' => $ghlContactId],
                        [
                            'name' => trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')) ?: 'Unnamed Contact',
                            'email' => $contact['email'] ?? null,
                            'phone' => $contact['phone'] ?? null,
                            'dnd' => (bool) ($contact['dnd'] ?? false),
                            'custom_fields' => json_encode($contact['customFields'] ?? []),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    $syncedCount++;
                }

                return $syncedCount;
            }

            Log::error('GHL Contacts API Error: ' . $response->body());
            return 0;

        } catch (\Exception $e) {
            Log::error('GHL Contacts API Exception: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fetch live appointments/calendars from GHL and sync to local database
     */
    public function syncAppointments(?string $locationId = null, ?string $accessToken = null): int
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/appointments/", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                $appointments = $response->json('appointments') ?? [];
                $syncedCount = 0;

                foreach ($appointments as $apt) {
                    $ghlAptId = $apt['id'] ?? null;
                    if (!$ghlAptId) continue;

                    DB::table('appointments')->updateOrInsert(
                        ['ghl_appointment_id' => $ghlAptId],
                        [
                            'calendar_id' => $apt['calendarId'] ?? null,
                            'contact_id' => $apt['contactId'] ?? null,
                            'title' => $apt['title'] ?? 'Scheduled Appointment',
                            'start_time' => $apt['startTime'] ?? null,
                            'end_time' => $apt['endTime'] ?? null,
                            'status' => $apt['appointmentStatus'] ?? 'booked',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    $syncedCount++;
                }

                return $syncedCount;
            }

            Log::error('GHL Appointments API Error: ' . $response->body());
            return 0;

        } catch (\Exception $e) {
            Log::error('GHL Appointments API Exception: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fetch live products/catalog from GHL and sync to local database
     */
    public function syncProducts(?string $locationId = null, ?string $accessToken = null): int
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/products/", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                $products = $response->json('products') ?? [];
                $syncedCount = 0;

                foreach ($products as $prod) {
                    $ghlProdId = $prod['id'] ?? null;
                    if (!$ghlProdId) continue;

                    DB::table('products')->updateOrInsert(
                        ['ghl_product_id' => $ghlProdId],
                        [
                            'name' => $prod['name'] ?? 'Unnamed Product',
                            'description' => $prod['description'] ?? '',
                            'price' => (float) ($prod['price'] ?? 0),
                            'type' => $prod['productType'] ?? 'service',
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    $syncedCount++;
                }

                return $syncedCount;
            }

            Log::error('GHL Products API Error: ' . $response->body());
            return 0;

        } catch (\Exception $e) {
            Log::error('GHL Products API Exception: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Fetch products directly for Estimator (tries GHL API, falls back to local database table)
     */
    public function fetchProducts(): array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if ($locationId && $accessToken) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Version' => '2021-07-28',
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/products/", [
                    'locationId' => $locationId,
                ]);

                if ($response->successful()) {
                    $products = $response->json('products') ?? [];
                    if (!empty($products)) {
                        return collect($products)->map(function ($p) {
                            return [
                                'id' => $p['id'] ?? null,
                                'name' => $p['name'] ?? 'Unnamed Product',
                                'description' => $p['description'] ?? '',
                                'price' => (float) ($p['price'] ?? 0),
                            ];
                        })->toArray();
                    }
                }
            } catch (\Exception $e) {
                Log::error('GHL Fetch Products Exception: ' . $e->getMessage());
            }
        }

        // Fallback to local products table if GHL API keys aren't set or return empty
        if (Schema::hasTable('products')) {
            return DB::table('products')->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description ?? '',
                    'price' => (float) $p->price,
                ];
            })->toArray();
        }

        return [];
    }

    /**
     * Push a new quote/opportunity to GoHighLevel
     */
    public function createOpportunity(array $quoteData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/opportunities/", [
                'locationId' => $locationId,
                'name' => ($quoteData['customer_name'] ?? 'Customer') . ' - Quote',
                'status' => 'open',
                'monetaryValue' => $quoteData['total_amount'] ?? 0,
                'pipelineType' => 'sales',
            ]);

            if ($response->successful()) {
                return $response->json('opportunity');
            }

            Log::error('GHL Create Opportunity Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('GHL Create Opportunity Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pulls pipeline opportunities by type (sales or recurring) from local database
     */
    public function getOpportunitiesByType(string $type = 'sales'): array
    {
        $query = DB::table('opportunities');

        if (Schema::hasColumn('opportunities', 'pipeline_type')) {
            $query->where('pipeline_type', $type);
        }

        $dbOpportunities = $query->get();

        if ($dbOpportunities->isNotEmpty()) {
            return $dbOpportunities->map(function ($item) use ($type) {
                return [
                    'id' => $item->id,
                    'ghl_id' => $item->ghl_opportunity_id ?? null,
                    'name' => $item->name,
                    'description' => $item->description ?? '',
                    'stage' => $item->stage ?? 'new',
                    'status' => $item->status ?? 'open',
                    'monetaryValue' => (float) ($item->value ?? 0),
                    'value' => (float) ($item->value ?? 0),
                    'time_in_stage' => $item->time_in_stage ?? '1d in stage',
                    'pipeline_type' => $item->pipeline_type ?? $type,
                    'hand_off' => (bool) ($item->hand_off ?? false),
                    'contact' => [
                        'id' => 'ghl_cnt_00' . $item->id,
                        'name' => $item->name,
                        'email' => strtolower(str_replace(' ', '.', $item->name)) . '@example.com',
                    ]
                ];
            })->toArray();
        }

        return [];
    }

    /**
     * Backward compatibility wrapper
     */
    public function getMockOpportunities(): array
    {
        return $this->getOpportunitiesByType('sales');
    }
}