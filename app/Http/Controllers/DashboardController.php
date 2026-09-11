<?php

namespace App\Http\Controllers;

use App\Models\CustomerJob;
use App\Models\Customer;
use App\Services\GoHighLevelService;

class DashboardController extends Controller
{
    protected $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    public function index()
    {
       // Agar tables mein data nahi hai toh testing ke liye dummy numbers show karein
    $totalRevenue = \App\Models\CustomerJob::count() > 0 
        ? \App\Models\CustomerJob::where('status', 'completed')->sum('net_profit') 
        : 5850.00;

    $activeJobsCount = \App\Models\CustomerJob::count() > 0 
        ? \App\Models\CustomerJob::where('status', 'in_progress')->count() 
        : 4;

    $totalCustomers = \App\Models\Customer::count() > 0 
        ? \App\Models\Customer::count() 
        : 42;

    try {
        $ghlOpportunities = $this->ghlService->getOpportunities() ?? [];
    } catch (\Exception $e) {
        $ghlOpportunities = [];
    }

    return view('dashboard', compact(
        'totalRevenue',
        'activeJobsCount',
        'totalCustomers',
        'ghlOpportunities'
    ));
    }
}