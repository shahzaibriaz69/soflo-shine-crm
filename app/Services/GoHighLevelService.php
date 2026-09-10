<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            return $this->getMockOpportunities();
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
            return $this->getMockOpportunities();

        } catch (\Exception $e) {
            Log::error('GHL API Exception: ' . $e->getMessage());
            return $this->getMockOpportunities();
        }
    }

    /**
     * Pulls pipeline opportunities directly from the local database
     * ensuring real-time drag-and-drop persistence.
     */
    public function getMockOpportunities(): array
    {
        $dbOpportunities = DB::table('opportunities')->get();

        if ($dbOpportunities->isNotEmpty()) {
            return $dbOpportunities->map(function ($item) {
                return [
                    'id'            => $item->id, // Database Primary ID for exact AJAX mapping
                    'ghl_id'        => $item->ghl_opportunity_id,
                    'name'          => $item->name,
                    'description'   => $item->description,
                    'stage'         => $item->stage,
                    'status'        => $item->status ?? 'open',
                    'monetaryValue' => (float) $item->value,
                    'value'         => (float) $item->value,
                    'time_in_stage' => $item->time_in_stage,
                    'hand_off'      => (bool) $item->hand_off,
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
}