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
}