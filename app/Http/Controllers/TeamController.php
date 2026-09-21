<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\GhlSyncController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    protected GhlSyncController $ghlSyncController;

    public function __construct(GhlSyncController $ghlSyncController)
    {
        $this->ghlSyncController = $ghlSyncController;
    }

    public function index()
    {
        $teamMembers = User::all();
        return view('team.index', compact('teamMembers'));
    }

    public function create()
    {
        return view('team.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string',
        ]);

        // 1. Create locally in database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'Staff',
        ]);

        // 2. Automatically push/sync to GoHighLevel
        try {
            $this->ghlSyncController->syncStaffToGHL($user);
        } catch (\Exception $e) {
            Log::error('Failed to sync team member to GHL: ' . $e->getMessage());
        }

        return redirect()->route('team.index')->with('success', 'Team member successfully created and synced with GHL!');
    }

    public function edit($id)
    {
        $member = User::findOrFail($id);
        return view('team.edit', compact('member'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'role' => 'nullable|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone ?? $user->phone,
            'role' => $request->role ?? $user->role,
        ]);

        // Optional: Sync update to GHL if needed
        try {
            $this->ghlSyncController->syncStaffToGHL($user);
        } catch (\Exception $e) {
            Log::error('Failed to update team member sync to GHL: ' . $e->getMessage());
        }

        return redirect()->route('team.index')->with('success', 'Team member successfully updated!');
    }

    public function destroy($id)
    {
        // Prevent deleting the currently logged-in user/admin
        if (auth()->id() == $id) {
            return redirect()->back()->with('error', 'You cannot delete your own active account!');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('team.index')->with('success', 'Team member deleted successfully!');
    }
}