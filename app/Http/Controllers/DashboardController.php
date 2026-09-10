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
        $totalRevenue = CustomerJob::where('status', 'completed')->sum('net_profit') ?? 0;
        $activeJobsCount = CustomerJob::where('status', 'in_progress')->count();
        $totalCustomers = Customer::count();

        // Data flow verification
    // dd($totalRevenue, $activeJobsCount, $totalCustomers, $ghlOpportunities);

        // Service se Dummy GHL Data
        $ghlOpportunities = $this->ghlService->getOpportunities();

        return view('dashboard', compact(
            'totalRevenue',
            'activeJobsCount',
            'totalCustomers',
            'ghlOpportunities'
        ));
    }
}