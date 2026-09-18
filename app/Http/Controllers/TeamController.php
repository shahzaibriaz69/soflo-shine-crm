<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index()
    {
        $teamMembers = \App\Models\User::all();
        return view('team.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('team.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string',
            'password' => 'required|min:6',
        ]);

        $ghlUserId = null;

        try {
            $baseUrl = config('services.ghl.base_url', 'https://services.leadconnectorhq.com');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ghl.api_key'),
                'Version' => '2021-07-28',
                'Content-Type' => 'application/json',
            ])->postr($baseUrl . '/users/', [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'] ?? '',
                        'role' => $validated['role'],
                        'locationId' => config('services.ghl.location_id'),
                    ]);

            if ($response->successful()) {
                $ghlUserId = $response->json('user.id') ?? $response->json('id') ?? null;
            } else {
                Log::error('GHL User Creation Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('GHL API Connection Error: ' . $e->getMessage());
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'ghl_user_id' => $ghlUserId,
        ]);

        return redirect()->route('team.index')->with('success', 'Team member successfully created and synced with GHL!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->ghl_user_id) {
            try {
                $baseUrl = config('services.ghl.base_url', 'https://services.leadconnectorhq.com');
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.ghl.api_key'),
                    'Version' => '2021-07-28',
                ])->delete($baseUrl . '/users/' . $user->ghl_user_id);
            } catch (\Exception $e) {
                Log::error('GHL User Deletion Error: ' . $e->getMessage());
            }
        }

        $user->delete();

        return redirect()->route('team.index')->with('success', 'Team member deleted successfully.');
    }
}