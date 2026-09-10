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
        // 1. Agar Live Credentials missing hain, toh Local Database se data fetch karein
        if (!$locationId || !$accessToken) {
            return $this->getOpportunitiesByType('sales');
        }

        // 2. Live GHL API Call
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version'       => '2021-07-28',
                'Accept'        => 'application/json',
            ])->get("{$this->baseUrl}/opportunities/search", [
                'location_id' => $locationId,
                'limit'       => 20,
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
     * Pulls pipeline opportunities by type (sales or recurring) from local database
     * ensuring real-time drag-and-drop persistence and tab filtering.
     */
    public function getOpportunitiesByType(string $type = 'sales'): array
    {
        $query = DB::table('opportunities');

        // Check karein ke pipeline_type column mojood hai ya nahi taake error na aaye
        if (Schema::hasColumn('opportunities', 'pipeline_type')) {
            $query->where('pipeline_type', $type);
        }

        $dbOpportunities = $query->get();

        if ($dbOpportunities->isNotEmpty()) {
            return $dbOpportunities->map(function ($item) use ($type) {
                return [
                    'id'            => $item->id, // Database Primary ID for exact AJAX mapping
                    'ghl_id'        => $item->ghl_opportunity_id ?? null,
                    'name'          => $item->name,
                    'description'   => $item->description ?? '',
                    'stage'         => $item->stage ?? 'new',
                    'status'        => $item->status ?? 'open',
                    'monetaryValue' => (float) ($item->value ?? 0),
                    'value'         => (float) ($item->value ?? 0),
                    'time_in_stage' => $item->time_in_stage ?? '1d in stage',
                    'pipeline_type' => $item->pipeline_type ?? $type,
                    'hand_off'      => (bool) ($item->hand_off ?? false),
                    'contact'       => [
                        'id'    => 'ghl_cnt_00' . $item->id,
                        'name'  => $item->name,
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