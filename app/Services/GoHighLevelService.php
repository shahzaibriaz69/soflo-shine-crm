<?php

namespace App\Services;

use App\Models\Contacts;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GoHighLevelService
{
    protected string $baseUrl = 'https://services.leadconnectorhq.com';

    public function isConfigured(): bool
    {
        return filled(config('services.ghl.api_key')) && filled(config('services.ghl.location_id'));
    }

    /**
     * Pull the configured HighLevel location into the local database.
     *
     * @return array{opportunities: int, contacts: int, users: int, appointments: int, products: int, errors: array<string, string>}
     */
    public function syncAll(): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('GHL_API_KEY or GHL_LOCATION_ID is missing from .env.');
        }

        $syncers = [
            'opportunities' => fn(): int => $this->syncOpportunitiesFromApi(),
            'contacts' => fn(): int => $this->syncContactsFromApi(),
            'users' => fn(): int => $this->syncUsersFromApi(),
            'appointments' => fn(): int => $this->syncAppointmentsFromApi(),
            'products' => fn(): int => $this->syncProductsFromApi(),
        ];
        $result = ['errors' => []];

        foreach ($syncers as $domain => $syncer) {
            try {
                $result[$domain] = $syncer();
            } catch (\Throwable $exception) {
                Log::warning("GHL {$domain} sync failed: " . $exception->getMessage());
                $result[$domain] = 0;
                $result['errors'][$domain] = $exception->getMessage();
            }
        }

        return $result;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->withToken(config('services.ghl.api_key'))
            ->withHeaders(['Version' => '2021-07-28'])
            ->timeout(20);
    }

    /** @return array<int, array<string, mixed>> */
    private function getCollection(string $endpoint, string $key, array $query = []): array
    {
        $query = array_merge([
            'locationId' => config('services.ghl.location_id'),
            'limit' => 100,
        ], $query);

        $response = $this->client()->get("{$this->baseUrl}{$endpoint}", $query);

        if (!$response->successful()) {
            Log::error('GHL API Request Failed', [
                'endpoint' => $endpoint,
                'query' => $query,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new \RuntimeException(
                "GHL request to {$endpoint} failed ({$response->status()}): " . $response->body()
            );
        }

        return $response->json($key) ?? $response->json() ?? [];
    }

    private function syncOpportunitiesFromApi(): int
    {
        // GHL opportunities search requires 'location_id' with underscore
        $locationId = config('services.ghl.location_id');
        $response = $this->client()->get("{$this->baseUrl}/opportunities/search", [
            'location_id' => $locationId,
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("GHL opportunities fetch failed: " . $response->body());
        }

        $opportunities = $response->json('opportunities') ?? [];

        foreach ($opportunities as $opportunity) {
            $values = [
                'name' => $opportunity['name'] ?? 'Unnamed Opportunity',
                'status' => $opportunity['status'] ?? 'open',
                'value' => $opportunity['monetaryValue'] ?? 0,
                'updated_at' => now(),
            ];
            $stageColumn = Schema::hasColumn('opportunities', 'stage_location') ? 'stage_location' : 'stage';
            $values[$stageColumn] = $opportunity['pipelineStageId'] ?? $opportunity['stageId'] ?? 'new';

            DB::table('opportunities')->updateOrInsert(
                ['ghl_opportunity_id' => $opportunity['id']],
                $values + ['created_at' => now()],
            );
        }

        return count($opportunities);
    }

    private function syncContactsFromApi(): int
    {
        $contacts = $this->getCollection('/contacts/', 'contacts');

        foreach ($contacts as $contact) {
            DB::table('contacts')->updateOrInsert(
                ['ghl_contact_id' => $contact['id']],
                [
                    'name' => trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? '')) ?: 'Unnamed Contact',
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'] ?? null,
                    'dnd' => (bool) ($contact['dnd'] ?? false),
                    'custom_fields' => json_encode($contact['customFields'] ?? []),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        return count($contacts);
    }

    private function syncUsersFromApi(): int
    {
        $locationId = config('services.ghl.location_id');
        
        // Users endpoint can accept locationId or return location users
        $response = $this->client()->get("{$this->baseUrl}/users/", [
            'locationId' => $locationId,
        ]);

        if (!$response->successful()) {
            Log::warning("GHL users fetch with locationId failed, trying without params: " . $response->body());
            $response = $this->client()->get("{$this->baseUrl}/users/");
        }

        if (!$response->successful()) {
            throw new \RuntimeException("GHL users fetch failed: " . $response->body());
        }

        $users = $response->json('users') ?? $response->json() ?? [];

        $saved = 0;
        foreach ($users as $user) {
            if (empty($user['email'])) {
                continue;
            }

            // Check if ghl_user_id column exists or fallback to email matching
            $matchCriteria = Schema::hasColumn('users', 'ghl_user_id') 
                ? ['ghl_user_id' => $user['id'] ?? $user['_id'] ?? null] 
                : ['email' => $user['email']];

            User::updateOrCreate(
                $matchCriteria,
                [
                    'name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: $user['email'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? null,
                    'role' => $user['role'] ?? 'account-user',
                    'password' => bcrypt(str()->random(40)),
                ],
            );
            $saved++;
        }

        return $saved;
    }

    private function syncAppointmentsFromApi(): int
    {
        $locationId = config('services.ghl.location_id');
        $contacts = Contacts::get(['ghl_contact_id']);

        if ($contacts->isEmpty()) {
            return 0;
        }

        $appointments = [];

        foreach ($contacts as $contact) {
            if (empty($contact->ghl_contact_id)) {
                continue;
            }

            try {
                $events = $this->getCollection(
                    '/contacts/' . $contact->ghl_contact_id . '/appointments',
                    'appointments',
                    ['showDrafted' => true]
                );

                if (!empty($events)) {
                    $appointments = array_merge($appointments, $events);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $saved = 0;
        foreach ($appointments as $appointment) {
            if (empty($appointment['id'])) {
                continue;
            }

            DB::table('appointments')->updateOrInsert(
                ['ghl_appointment_id' => $appointment['id']],
                [
                    'calendar_id' => $appointment['calendarId'] ?? $appointment['calendar_id'] ?? null,
                    'contact_id' => $appointment['contactId'] ?? $appointment['contact_id'] ?? null,
                    'title' => $appointment['title'] ?? $appointment['appointmentTitle'] ?? 'GHL Appointment',
                    'start_time' => $appointment['startTime'] ?? $appointment['start_time'] ?? null,
                    'end_time' => $appointment['endTime'] ?? $appointment['end_time'] ?? null,
                    'status' => $appointment['appointmentStatus'] ?? $appointment['status'] ?? 'booked',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $saved++;
        }

        return $saved;
    }

    private function syncProductsFromApi(): int
    {
        $response = $this->client()->get("{$this->baseUrl}/products/", [
            'locationId' => config('services.ghl.location_id'),
        ]);

        if (!$response->successful()) {
            // Fallback if endpoint differs
            $response = $this->client()->get("{$this->baseUrl}/products");
        }

        if (!$response->successful()) {
            Log::warning("GHL products fetch failed: " . $response->body());
            return 0;
        }

        $products = $response->json('products') ?? $response->json() ?? [];
        $saved = 0;

        foreach ($products as $product) {
            $productId = $product['id'] ?? $product['_id'] ?? null;
            if (!$productId) {
                continue;
            }

            DB::table('products')->updateOrInsert(
                ['ghl_product_id' => $productId],
                [
                    'name' => $product['name'] ?? 'Unnamed Product',
                    'description' => $product['description'] ?? null,
                    'price' => $product['price'] ?? $product['amount'] ?? 0,
                    'type' => $product['productType'] ?? $product['type'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
            $saved++;
        }

        return $saved;
    }

    public function getOpportunities(?string $locationId = null, ?string $accessToken = null): array
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return $this->getOpportunitiesByType('sales');
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/opportunities/search", [
                'location_id' => $locationId,
                'limit' => 20,
            ]);

            if ($response->successful()) {
                return $response->json('opportunities') ?? [];
            }

            return $this->getOpportunitiesByType('sales');
        } catch (\Exception $e) {
            return $this->getOpportunitiesByType('sales');
        }
    }

    public function getGhlPipelines(): array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/opportunities/pipelines", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                return $response->json('pipelines') ?? [];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createOpportunity(array $quoteData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return null;
        }

        try {
            $pipelines = $this->getGhlPipelines();
            $pipelineId = null;
            $stageId = null;

            if (!empty($pipelines)) {
                $firstPipeline = $pipelines[0];
                $pipelineId = $firstPipeline['id'] ?? null;

                if (!empty($firstPipeline['stages'])) {
                    $stageId = $firstPipeline['stages'][0]['id'] ?? null;
                }
            }

            $contactId = $quoteData['contact_id'] ?? $quoteData['ghl_contact_id'] ?? null;

            if (!$contactId || !$pipelineId) {
                return null;
            }

            $payload = [
                'locationId' => $locationId,
                'contactId' => $contactId,
                'pipelineId' => $pipelineId,
                'name' => ($quoteData['customer_name'] ?? 'Customer') . ' - Quote',
                'status' => 'open',
                'monetaryValue' => $quoteData['total_amount'] ?? 0,
            ];

            if ($stageId) {
                $payload['pipelineStageId'] = $stageId;
            }

            $response = $this->client()->withHeaders(['Content-Type' => 'application/json'])->post("{$this->baseUrl}/opportunities/", $payload);

            if ($response->successful()) {
                return $response->json('opportunity');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateOpportunity(string $ghlId, array $data): ?array
    {
        $accessToken = config('services.ghl.api_key');
        if (!$accessToken || !$ghlId) {
            return null;
        }

        try {
            $response = $this->client()->withHeaders(['Content-Type' => 'application/json'])->put("{$this->baseUrl}/opportunities/{$ghlId}", $data);

            if ($response->successful()) {
                return $response->json('opportunity');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteOpportunity(string $ghlId): bool
    {
        $accessToken = config('services.ghl.api_key');
        if (!$accessToken || !$ghlId) {
            return false;
        }

        try {
            $response = $this->client()->delete("{$this->baseUrl}/opportunities/{$ghlId}");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createContact(array $contactData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return null;
        }

        try {
            $payload = array_merge(['locationId' => $locationId], $contactData);
            $response = $this->client()->withHeaders(['Content-Type' => 'application/json'])->post("{$this->baseUrl}/contacts/", $payload);

            if ($response->successful()) {
                return $response->json('contact');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function sendSMS(string $phone, string $message): bool
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (!$locationId || !$accessToken) {
            return false;
        }

        try {
            $response = $this->client()->post("{$this->baseUrl}/conversations/messages", [
                'type' => 'SMS',
                'locationId' => $locationId,
                'phone' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getOpportunitiesByType(string $type = 'sales'): array
    {
        $query = DB::table('opportunities');

        if (Schema::hasColumn('opportunities', 'pipeline_type')) {
            $query->where('pipeline_type', $type);
        }

        $dbOpportunities = $query->get();

        if ($dbOpportunities->isNotEmpty()) {
            return $dbOpportunities->map(function ($item) use ($type) {
                return [
                    'id' => $item->id,
                    'ghl_id' => $item->ghl_opportunity_id ?? null,
                    'name' => $item->name,
                    'description' => $item->description ?? '',
                    'stage' => $item->stage ?? 'new',
                    'status' => $item->status ?? 'open',
                    'monetaryValue' => (float) ($item->value ?? 0),
                    'value' => (float) ($item->value ?? 0),
                    'time_in_stage' => $item->time_in_stage ?? '1d in stage',
                    'pipeline_type' => $item->pipeline_type ?? $type,
                    'hand_off' => (bool) ($item->hand_off ?? false),
                    'contact' => [
                        'id' => 'ghl_cnt_00' . $item->id,
                        'name' => $item->name,
                        'email' => strtolower(str_replace(' ', '.', $item->name)) . '@example.com',
                    ],
                ];
            })->toArray();
        }

        return [];
    }

    public function fetchProducts(): array
    {
        if (Schema::hasTable('products')) {
            return DB::table('products')->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description ?? '',
                    'price' => (float) $p->price,
                ];
            })->toArray();
        }

        return [];
    }

    public function syncOpportunities(): int
    {
        return $this->syncOpportunitiesFromApi();
    }

    public function syncContacts(): int
    {
        return $this->syncContactsFromApi();
    }

    public function syncUsers(): int
    {
        return $this->syncUsersFromApi();
    }

    public function syncAppointments(): int
    {
        return $this->syncAppointmentsFromApi();
    }

    public function syncProducts(): int
    {
        return $this->syncProductsFromApi();
    }
}