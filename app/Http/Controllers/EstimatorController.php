<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            'customer_email' => 'nullable|email',
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
            'phone' => $request->input('phone'),
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

        // Send Email using the mail folder template (mail.quote-email)
        $email = $request->input('customer_email');
        if ($email) {
            try {
                $emailData = [
                    'customerName' => $request->input('customer_name'),
                    'serviceName' => $result['product_name'] ?? 'Auto Detailing Package',
                    'size' => $request->input('size'),
                    'condition' => $request->input('condition'),
                    'totalAmount' => $result['total_estimate'],
                    'approvalUrl' => url('/quotes/approve/' . uniqid())
                ];

                Mail::send('mail.quote-email', $emailData, function($message) use ($email) {
                    $message->to($email)
                            ->subject('Your Official SoFlo Shine Detailing Quote');
                });
            } catch (\Exception $e) {
                Log::error('Quote Email Notification Failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Quote successfully generated, synced to GHL, SMS & Email triggered! Total: $' . $result['total_estimate']);
    }

    public function quotes()
    {
        $products = $this->estimatorService->getAvailableProducts();
        
        // Joined with customers table to fetch customer name for the history view
        $quotes = DB::table('quotes')
            ->leftJoin('customers', 'quotes.customer_id', '=', 'customers.id')
            ->select('quotes.*', 'customers.name as customer_name', 'customers.phone as customer_phone')
            ->orderBy('quotes.created_at', 'desc')
            ->get();

        return view('quotes', compact('quotes', 'products'));
    }
}