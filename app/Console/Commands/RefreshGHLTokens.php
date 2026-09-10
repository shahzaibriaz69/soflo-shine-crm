<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshGHLTokens extends Command
{
    protected $signature = 'ghl:refresh-tokens';
    protected $description = 'Expire hone se pehle GHL Access Tokens auto refresh karein';

    public function handle()
    {
        // Un tokens ko fetch karein jo agle 2 ghante mein expire hone wale hain
        $integrations = DB::table('ghl_integrations')
            ->where('expires_at', '<=', now()->addHours(2))
            ->get();

        if ($integrations->isEmpty()) {
            $this->info('Sabhi tokens valid hain. Refresh ki zaroorat nahi.');
            return;
        }

        foreach ($integrations as $integration) {
            try {
                $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
                    'client_id'     => config('services.gohighlevel.client_id'),
                    'client_secret' => config('services.gohighlevel.client_secret'),
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $integration->refresh_token,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    DB::table('ghl_integrations')
                        ->where('id', $integration->id)
                        ->update([
                            'access_token'  => $data['access_token'],
                            'refresh_token' => $data['refresh_token'],
                            'expires_at'    => now()->addSeconds($data['expires_in']),
                            'updated_at'    => now(),
                        ]);

                    $this->info("Location ID {$integration->location_id} ka token refresh ho gaya.");
                } else {
                    Log::error("GHL Token Refresh Failed [Location: {$integration->location_id}]: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("GHL Token Refresh Exception: " . $e->getMessage());
            }
        }
    }
}