<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Service;
use App\Models\Package;
use App\Models\Customer;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with('customer')->latest()->get();
        return view('quotes.index', compact('quotes'));
    }

    public function create()
    {
        $services = Service::all();
        $packages = Package::all();
        $customers = Customer::all();
        return view('quotes.create', compact('services', 'packages', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'nullable|exists:packages,id',
            'service_id' => 'nullable|exists:services,id',
            'estimated_price' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        Quote::create([
            'customer_id' => $validated['customer_id'],
            'package_id' => $validated['package_id'] ?? null,
            'service_id' => $validated['service_id'] ?? null,
            'estimated_price' => $validated['estimated_price'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('quotes.index')->with('success', 'Quote created successfully!');
    }
}