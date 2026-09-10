<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GHLAuthController extends Controller
{
    public function redirectToGHL()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.gohighlevel.client_id'),
            'redirect_uri'  => config('services.gohighlevel.redirect_uri'),
            'scope'         => 'contacts.readonly opportunities.readonly calendars.readonly',
        ]);

        return redirect('https://marketplace.gohighlevel.com/oauth/chooselocation?' . $query);
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('dashboard')->with('error', 'Authorization failed!');
        }

        try {
            //Access Token exchange
            $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
                'client_id'     => config('services.gohighlevel.client_id'),
                'client_secret' => config('services.gohighlevel.client_secret'),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Token DB Save/Update
                DB::table('ghl_integrations')->updateOrInsert(
                    ['location_id' => $data['locationId']],
                    [
                        'access_token'  => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires_at'    => now()->addSeconds($data['expires_in']),
                        'updated_at'    => now(),
                    ]
                );

                return redirect()->route('dashboard')->with('success', 'GHL Account Connected Successfully!');
            }

            Log::error('GHL OAuth Error: ' . $response->body());
            return redirect()->route('dashboard')->with('error', 'Failed to connect GHL.');

        } catch (\Exception $e) {
            Log::error('GHL Exception: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Something went wrong.');
        }
    }
}