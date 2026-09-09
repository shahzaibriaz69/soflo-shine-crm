<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Package::create($validated);

        return redirect()->back()->with('success', 'Package added successfully!');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->back()->with('success', 'Package deleted successfully!');
    }
}