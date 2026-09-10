<?php

namespace App\Http\Controllers;

use App\Models\RequestQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RequestQuoteController extends Controller
{
    public function index()
    {
        if (RequestQuote::count() === 0) {
            $dummyData = [
                [
                    'source_type' => 'instagram',
                    'source_label' => 'Instagram DM',
                    'time_ago' => '12 min ago',
                    'badge_status' => 'new',
                    'name' => 'Ashley Nguyen',
                    'customer_name' => 'Ashley Nguyen',
                    'email' => 'ashley@example.com',
                    'phone' => '(305) 555-0147',
                    'reference_code' => 'RO-118',
                    'vehicle_title' => '2021 Tesla Model Y',
                    'vehicle_type' => 'Crossover / Mid SUV',
                    'vehicle_details' => '2021 Tesla Model Y - Crossover',
                    'service_requested' => 'Interior + Exterior Full Detail',
                    'guide_price' => 402,
                    'preferred_day' => 'Sat Aug 22 - morning',
                    'notes' => 'Dog rides in the back — lots of hair. Juice spill on the second row.',
                    'photos' => json_encode(['Exterior — front 3/4', 'Interior — rear seats', 'Interior — cargo'])
                ],
                [
                    'source_type' => 'website',
                    'source_label' => 'Website form',
                    'time_ago' => '1 hr ago',
                    'badge_status' => 'new',
                    'name' => 'Devon Pierce',
                    'customer_name' => 'Devon Pierce',
                    'email' => 'devon@example.com',
                    'phone' => '(786) 555-0193',
                    'reference_code' => 'RO-117',
                    'vehicle_title' => '2019 Ford F-150',
                    'vehicle_type' => 'Full-size SUV / Truck',
                    'vehicle_details' => '2019 Ford F-150 - Truck',
                    'service_requested' => 'Full Exterior Detail',
                    'guide_price' => 293,
                    'preferred_day' => 'Fri Aug 21 - afternoon',
                    'notes' => 'Work truck. Heavy water spots on the hood — asked about ceramic pricing.',
                    'photos' => json_encode(['Exterior — driver side', 'Exterior — hood'])
                ],
                [
                    'source_type' => 'tiktok',
                    'source_label' => 'TikTok comment',
                    'time_ago' => '3 hrs ago',
                    'badge_status' => 'new',
                    'name' => 'Camila Ortiz',
                    'customer_name' => 'Camila Ortiz',
                    'email' => 'camila@example.com',
                    'phone' => '(305) 555-0128',
                    'reference_code' => 'RO-116',
                    'vehicle_title' => '2023 Honda Civic',
                    'vehicle_type' => 'Coupe / Sedan',
                    'vehicle_details' => '2023 Honda Civic - Sedan',
                    'service_requested' => 'Full Interior Detail',
                    'guide_price' => 185,
                    'preferred_day' => 'Sun Aug 23 - any time',
                    'notes' => 'Saw the interior transformation video and wants the same on hers.',
                    'photos' => json_encode(['Interior — front', 'Interior — rear'])
                ],
                [
                    'source_type' => 'facebook',
                    'source_label' => 'Facebook',
                    'time_ago' => 'Yesterday',
                    'badge_status' => 'quoted',
                    'name' => 'Marcus Webb',
                    'customer_name' => 'Marcus Webb',
                    'email' => 'marcus@example.com',
                    'phone' => '(954) 555-0166',
                    'reference_code' => 'RO-115',
                    'vehicle_title' => '2020 Chevy Tahoe',
                    'vehicle_type' => 'Full-size SUV / Truck',
                    'vehicle_details' => '2020 Chevy Tahoe - SUV',
                    'service_requested' => 'Express Wash & Shine',
                    'guide_price' => 85,
                    'preferred_day' => 'Mon Aug 24 - morning',
                    'notes' => 'Wants it clean before a family trip on Tuesday.',
                    'photos' => json_encode(['Exterior — rear 3/4'])
                ]
            ];

            foreach ($dummyData as $data) {
                if (!Schema::hasColumn('request_quotes', 'customer_name')) {
                    unset($data['customer_name']);
                }
                if (!Schema::hasColumn('request_quotes', 'email')) {
                    unset($data['email']);
                }
                if (!Schema::hasColumn('request_quotes', 'vehicle_details')) {
                    unset($data['vehicle_details']);
                }
                RequestQuote::create($data);
            }
        }

        $quoteRequests = RequestQuote::latest()->get();

        foreach ($quoteRequests as $req) {
            if (is_string($req->photos)) {
                $req->photos = json_decode($req->photos, true);
            }
        }

        $newRequestsCount = Schema::hasColumn('request_quotes', 'badge_status')
            ? RequestQuote::where('badge_status', 'new')->count()
            : $quoteRequests->where('badge_status', 'new')->count();

        return view('requests', compact('quoteRequests', 'newRequestsCount'));
    }

    public function buildQuote($id)
    {
        $quoteRequest = RequestQuote::find($id);
        
        if ($quoteRequest) {
            \App\Models\Opportunity::create([
                'name' => $quoteRequest->vehicle_title . ' — ' . $quoteRequest->name,
                'description' => $quoteRequest->service_requested . ' · ' . $quoteRequest->phone,
                'value' => $quoteRequest->guide_price,
                'stage' => 'new',
                'time_in_stage' => '1d in stage',
                'pipeline_type' => 'sales',
            ]);

            $quoteRequest->delete();
        }

        return redirect()->route('pipelines.index')->with('success', 'Quote request converted to pipeline successfully!');
    }

    public function decline($id)
    {
        $quoteRequest = RequestQuote::find($id);
        if ($quoteRequest) {
            $quoteRequest->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Quote request declined and removed successfully.'
        ]);
    }
}