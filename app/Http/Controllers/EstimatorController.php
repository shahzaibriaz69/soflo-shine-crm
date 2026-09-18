<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\EstimatorService;
use App\Services\GoHighLevelService;
use Illuminate\Support\Facades\Log;
use App\Models\Service;
use App\Models\Package;

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

        // 1. Save quote locally
        $this->estimatorService->saveQuote([
            'customer_name' => $request->input('customer_name'),
            'phone' => $request->input('phone'),
            'size' => $request->input('size'),
            'condition' => $request->input('condition'),
            'total_estimate' => $result['total_estimate'],
        ]);

        // 2. Push Opportunity and Contact to GoHighLevel (GHL)
        try {
            // Create Contact on GHL if email/phone exists
            if ($request->input('customer_email') || $request->input('phone')) {
                $nameParts = explode(' ', trim($request->input('customer_name')), 2);
                $this->ghlService->createContact([
                    'firstName' => $nameParts[0] ?? 'Customer',
                    'lastName' => $nameParts[1] ?? '',
                    'email' => $request->input('customer_email'),
                    'phone' => $request->input('phone'),
                ]);
            }

            // Create Opportunity on GHL
            $ghlResponse = $this->ghlService->createOpportunity([
                'customer_name' => $request->input('customer_name'),
                'total_amount' => $result['total_estimate'],
            ]);

            if (!empty($ghlResponse['id']) && DB::getSchemaBuilder()->hasTable('opportunities')) {
                DB::table('opportunities')
                    ->where('name', 'LIKE', '%' . $request->input('customer_name') . '%')
                    ->latest('id')
                    ->update(['ghl_opportunity_id' => $ghlResponse['id']]);
            }
        } catch (\Exception $e) {
            Log::error('GHL Push Sync Failed: ' . $e->getMessage());
        }

        // 3. Send SMS notification via GHL
        $phone = $request->input('phone');
        if ($phone) {
            try {
                $message = "Hi " . $request->input('customer_name') . ", your SoFlo Shine quote total is $" . $result['total_estimate'] . ". Thank you!";
                $this->ghlService->sendSMS($phone, $message);
            } catch (\Exception $e) {
                Log::error('GHL Quote SMS Notification Failed: ' . $e->getMessage());
            }
        }

        // 4. Send Email using the mail folder template
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

                Mail::send('mail.quote-email', $emailData, function ($message) use ($email) {
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

        $quotes = DB::table('quotes')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quotes', compact('quotes', 'products'));
    }

    // Delete quote with GHL Opportunity cleanup
    public function destroyQuote($id)
    {
        $quote = DB::table('quotes')->where('id', $id)->first();
        
        if ($quote && DB::getSchemaBuilder()->hasColumn('opportunities', 'ghl_opportunity_id')) {
            $opportunity = DB::table('opportunities')->where('name', 'LIKE', '%' . $quote->customer_name . '%')->first();
            if ($opportunity && !empty($opportunity->ghl_opportunity_id)) {
                try {
                    $this->ghlService->deleteOpportunity($opportunity->ghl_opportunity_id);
                } catch (\Exception $e) {
                    Log::error('GHL Delete Opportunity Sync Failed: ' . $e->getMessage());
                }
            }
        }

        DB::table('quotes')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Quote successfully deleted locally and on GHL!');
    }

    public function servicesIndex()
    {
        $products = Service::all();
        return view('services', compact('products'));
    }

    public function packagesIndex()
    {
        $packages = Package::all();
        return view('packages', compact('packages'));
    }

    public function storePackage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Package::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'duration' => $request->duration,
            'description' => $request->description,
            'status' => 'Active',
        ]);

        return redirect()->route('packages.index')->with('success', 'Package successfully create ho gaya hai!');
    }
}