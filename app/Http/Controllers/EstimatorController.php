<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EstimatorService;
use App\Services\GoHighLevelService;
use Illuminate\Support\Facades\Log;

class EstimatorController extends Controller
{
    protected EstimatorService $estimatorService;
    protected GoHighLevelService $ghlService;

    public function __construct(EstimatorService $estimatorService, GoHighLevelService $ghlService)
    {
        $this->estimatorService = $estimatorService;
        $this->ghlService = $ghlService;
    }

    public function index()
    {
        $products = $this->estimatorService->getAvailableProducts();
        return view('estimator', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'size' => 'required|string',
            'condition' => 'required|string',
            'customer_name' => 'required|string',
            'phone' => 'nullable|string',
        ]);

        $result = $this->estimatorService->calculateEstimate(
            $request->input('product_id'),
            $request->input('size'),
            $request->input('condition')
        );

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Save quote locally
        $this->estimatorService->saveQuote([
            'customer_name' => $request->input('customer_name'),
            'size' => $request->input('size'),
            'condition' => $request->input('condition'),
            'total_estimate' => $result['total_estimate'],
        ]);

        $phone = $request->input('phone');
        if ($phone) {
            try {
                $message = "Hi " . $request->input('customer_name') . ", your SoFlo Shine quote total is $" . $result['total_estimate'] . ". Thank you!";
                $this->ghlService->sendSMS($phone, $message);
            } catch (\Exception $e) {
                Log::error('GHL Quote SMS Notification Failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Quote successfully generated, synced to GHL, and SMS triggered! Total: $' . $result['total_estimate']);
    }

    public function quotes()
    {
        $products = $this->estimatorService->getAvailableProducts();
        $quotes = DB::table('quotes')->orderBy('created_at', 'desc')->get();

        return view('quotes', compact('quotes', 'products'));
    }
}