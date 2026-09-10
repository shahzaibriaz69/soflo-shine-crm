<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoHighLevelService
{
    protected string $baseUrl = 'https://services.leadconnectorhq.com';

    /**
     * Fetch Live Opportunities or fallback to Mock Data
     */
    public function getOpportunities(?string $locationId = null, ?string $accessToken = null): array
    {
        // 1. Agar Credentials missing hon, toh Mock/Dummy Data return karein
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
     * Aapka Existing Mock/Dummy Data
     */
    public function getMockOpportunities(): array
    {
        return [
            [
                'id' => 'ghl_opt_1001',
                'name' => 'Complete Detailing Package - John D.',
                'status' => 'open',
                'monetaryValue' => 350.00,
                'contact' => [
                    'id' => 'ghl_cnt_001',
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                ]
            ],
            [
                'id' => 'ghl_opt_1002',
                'name' => 'Ceramic Coating Prep - Sarah C.',
                'status' => 'won',
                'monetaryValue' => 850.00,
                'contact' => [
                    'id' => 'ghl_cnt_002',
                    'name' => 'Sarah Connor',
                    'email' => 'sarah.c@skynet.com',
                ]
            ],
        ];
    }
}