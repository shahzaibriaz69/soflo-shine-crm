<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GoHighLevelService
{
    protected string $baseUrl = 'https://services.leadconnectorhq.com';

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

    public function getGhlPipelines(): array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/opportunities/pipelines", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                return $response->json('pipelines') ?? [];
            }

            Log::error('GHL Fetch Pipelines Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('GHL Fetch Pipelines Exception: ' . $e->getMessage());
            return [];
        }
    }

    public function createOpportunity(array $quoteData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return null;
        }

        try {
            // GHL se automatically pipeline aur stage fetch kar rahe hain
            $pipelines = $this->getGhlPipelines();
            $pipelineId = null;
            $stageId = null;

            if (!empty($pipelines)) {
                $firstPipeline = $pipelines[0];
                $pipelineId = $firstPipeline['id'] ?? null;
                
                if (!empty($firstPipeline['stages'])) {
                    $stageId = $firstPipeline['stages'][0]['id'] ?? null;
                }
            }

            $payload = [
                'locationId' => $locationId,
                'name' => ($quoteData['customer_name'] ?? 'Customer') . ' - Quote',
                'status' => 'open',
                'monetaryValue' => $quoteData['total_amount'] ?? 0,
            ];

            if ($pipelineId) {
                $payload['pipelineId'] = $pipelineId;
            }
            if ($stageId) {
                $payload['stageId'] = $stageId;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/opportunities/", $payload);

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

    public function sendSMS(string $phone, string $message): bool
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/conversations/messages", [
                'type' => 'SMS',
                'locationId' => $locationId,
                'phone' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('GHL SMS Exception: ' . $e->getMessage());
            return false;
        }
    }

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

    public function fetchProducts(): array
    {
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

    // --- Sync Methods with Local Database Storage ---

    public function syncOpportunities(): int
    {
        $opportunities = $this->getOpportunities();
        $count = 0;

        foreach ($opportunities as $opp) {
            if (Schema::hasTable('opportunities')) {
                DB::table('opportunities')->updateOrInsert(
                    ['ghl_opportunity_id' => $opp['id'] ?? null],
                    [
                        'name' => $opp['name'] ?? 'Unnamed Opportunity',
                        'stage' => $opp['stage'] ?? 'new',
                        'status' => $opp['status'] ?? 'open',
                        'value' => $opp['monetaryValue'] ?? ($opp['value'] ?? 0),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            $count++;
        }

        return $count;
    }

    public function syncContacts(): int
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

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
                'limit' => 50,
            ]);

            if ($response->successful()) {
                $contacts = $response->json('contacts') ?? [];
                $count = 0;

                foreach ($contacts as $contact) {
                    if (Schema::hasTable('contacts')) {
                        DB::table('contacts')->updateOrInsert(
                            ['email' => $contact['email'] ?? null],
                            [
                                'name' => trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')),
                                'phone' => $contact['phone'] ?? null,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }
                    $count++;
                }

                return $count;
            }
        } catch (\Exception $e) {
            Log::error('GHL Sync Contacts Exception: ' . $e->getMessage());
        }

        return 0;
    }

    public function syncAppointments(): int
    {
        return 0;
    }

    public function syncProducts(): int
    {
        $products = $this->fetchProducts();
        return count($products);
    }
}