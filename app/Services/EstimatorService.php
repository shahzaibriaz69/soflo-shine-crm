<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use App\Services\GoHighLevelService;

class EstimatorService
{
    protected GoHighLevelService $ghlService;

    public function __construct(GoHighLevelService $ghlService)
    {
        $this->ghlService = $ghlService;
    }

    /**
     * Get all products directly from GHL for the estimator view.
     */
    public function getAvailableProducts(): array
    {
        return $this->ghlService->fetchProducts();
    }

    /**
     * Calculate auto-detailing price based on GHL product base price, vehicle size, and condition modifiers.
     */
    public function calculateEstimate($productId, string $size = 'medium', string $condition = 'standard'): array
    {
        // Fetch products directly from GHL source
        $products = $this->ghlService->fetchProducts();

        $product = collect($products)->firstWhere('id', $productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found in GoHighLevel catalog.'
            ];
        }

        $basePrice = (float) $product['price'];

        $sizeMultipliers = [
            'small' => 0.9,
            'medium' => 1.0,
            'large' => 1.25,
            'xl' => 1.5,
        ];

        $conditionAddons = [
            'standard' => 0.0,
            'moderate' => 50.0,
            'severe' => 120.0,
        ];

        $multiplier = $sizeMultipliers[$size] ?? 1.0;
        $addon = $conditionAddons[$condition] ?? 0.0;

        $subtotal = ($basePrice * $multiplier) + $addon;
        $tax = $subtotal * 0.07;
        $total = $subtotal + $tax;

        return [
            'success' => true,
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'base_price' => $basePrice,
            'size' => $size,
            'condition' => $condition,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total_estimate' => round($total, 2),
        ];
    }

    /**
     * Save quote locally and sync to GoHighLevel as an Opportunity.
     */
    public function saveQuote(array $data): int
    {
        $customerName = trim((string) ($data['customer_name'] ?? $data['customer'] ?? 'Walk-in Customer')) ?: 'Walk-in Customer';
        
        $customer = Customer::firstOrCreate(
            ['name' => $customerName],
            [
                'email' => strtolower(str_replace(' ', '.', $customerName)) . '@walkin.local',
                'phone' => $data['phone'] ?? '',
                'address' => null,
                'plan_type' => 'one_time',
                'lifetime_value' => 0.00,
            ]
        );

        $sizeKey = strtolower((string) ($data['size'] ?? 'medium'));
        $conditionKey = strtolower((string) ($data['condition'] ?? 'standard'));
        $totalAmount = (float) ($data['total_estimate'] ?? 0);

        // Check if the quotes table has a 'customer' column or if it uses relationships
        $quoteData = [
            'quote_number' => 'Q-' . strtoupper(substr(md5((string) now()->timestamp . $customerName . random_int(1000, 9999)), 0, 8)),
            'customer_id' => $customer->id,
            'vehicle_size_multiplier' => [
                'small' => 0.9,
                'sedan' => 0.9,
                'medium' => 1.0,
                'midsize' => 1.0,
                'large' => 1.25,
                'truck' => 1.25,
                'xl' => 1.5,
            ][$sizeKey] ?? 1.0,
            'condition_multiplier' => [
                'good' => 1.0,
                'average' => 1.1,
                'standard' => 1.0,
                'moderate' => 1.2,
                'dirty' => 1.25,
                'severe' => 1.4,
            ][$conditionKey] ?? 1.0,
            'total_amount' => $totalAmount,
            'status' => 'draft',
        ];

        // Safely add customer name text column if the schema supports it to prevent blank table views
        if (\Illuminate\Support\Facades\Schema::hasColumn('quotes', 'customer')) {
            $quoteData['customer'] = $customerName;
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('quotes', 'customer_name')) {
            $quoteData['customer_name'] = $customerName;
        }

        $quote = Quote::create($quoteData);

        $this->ghlService->createOpportunity([
            'customer_name' => $customerName,
            'total_amount' => $totalAmount,
        ]);

        return $quote->id;
    }
}