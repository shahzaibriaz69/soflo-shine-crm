<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\EstimatorService;
use App\Services\GoHighLevelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatorSaveQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_quote_using_current_database_schema(): void
    {
        Customer::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '5551234567',
            'address' => '123 Main St',
            'plan_type' => 'one_time',
            'lifetime_value' => 0.00,
        ]);

        $service = new EstimatorService(new class extends GoHighLevelService {
            public function __construct() {}

            public function createOpportunity(array $quoteData): ?array
            {
                return null;
            }
        });

        $quoteId = $service->saveQuote([
            'customer_name' => 'Jane Smith',
            'size' => 'large',
            'condition' => 'severe',
            'total_estimate' => 245.67,
        ]);

        $this->assertNotNull($quoteId);
        $this->assertDatabaseHas('quotes', [
            'customer_id' => 1,
            'total_amount' => '245.67',
            'status' => 'draft',
        ]);
    }
}
