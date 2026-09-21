<?php

namespace App\Services;

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
        if (! $this->isConfigured()) {
            throw new \RuntimeException('GHL_API_KEY or GHL_LOCATION_ID is missing from .env.');
        }

        $syncers = [
            'opportunities' => fn (): int => $this->syncOpportunitiesFromApi(),
            'contacts' => fn (): int => $this->syncContactsFromApi(),
            'users' => fn (): int => $this->syncUsersFromApi(),
            'appointments' => fn (): int => $this->syncAppointmentsFromApi(),
            'products' => fn (): int => $this->syncProductsFromApi(),
        ];
        $result = ['errors' => []];

        foreach ($syncers as $domain => $syncer) {
            try {
                $result[$domain] = $syncer();
            } catch (\Throwable $exception) {
                Log::warning("GHL {$domain} sync failed: ".$exception->getMessage());
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
    private function getCollection(string $endpoint, string $key): array
    {
        $locationParameter = $endpoint === '/opportunities/search' ? 'location_id' : 'locationId';
        $response = $this->client()->get("{$this->baseUrl}{$endpoint}", [
            $locationParameter => config('services.ghl.location_id'),
            'limit' => 100,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("GHL request to {$endpoint} failed ({$response->status()}).");
        }

        return $response->json($key) ?? [];
    }

    private function syncOpportunitiesFromApi(): int
    {
        $opportunities = $this->getCollection('/opportunities/search', 'opportunities');

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
                    'name' => trim(($contact['firstName'] ?? '').' '.($contact['lastName'] ?? '')) ?: 'Unnamed Contact',
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
        $users = $this->getCollection('/users/', 'users');

        foreach ($users as $user) {
            if (empty($user['email'])) {
                continue;
            }

            User::updateOrCreate(
                ['ghl_user_id' => $user['id']],
                [
                    'name' => trim(($user['firstName'] ?? '').' '.($user['lastName'] ?? '')) ?: $user['email'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? null,
                    'role' => $user['role'] ?? 'account-user',
                    'password' => bcrypt(str()->random(40)),
                ],
            );
        }

        return count($users);
    }

    private function syncAppointmentsFromApi(): int
    {
        $appointments = $this->getCollection('/calendars/events', 'events');

        foreach ($appointments as $appointment) {
            DB::table('appointments')->updateOrInsert(
                ['ghl_appointment_id' => $appointment['id']],
                [
                    'calendar_id' => $appointment['calendarId'] ?? null,
                    'contact_id' => $appointment['contactId'] ?? null,
                    'title' => $appointment['title'] ?? $appointment['appointmentTitle'] ?? null,
                    'start_time' => $appointment['startTime'] ?? null,
                    'end_time' => $appointment['endTime'] ?? null,
                    'status' => $appointment['appointmentStatus'] ?? 'booked',
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        return count($appointments);
    }

    private function syncProductsFromApi(): int
    {
        $products = $this->getCollection('/products/', 'products');

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['ghl_product_id' => $product['id']],
                [
                    'name' => $product['name'] ?? 'Unnamed Product',
                    'description' => $product['description'] ?? null,
                    'price' => $product['price'] ?? $product['amount'] ?? 0,
                    'type' => $product['productType'] ?? $product['type'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        return count($products);
    }

    public function getOpportunities(?string $locationId = null, ?string $accessToken = null): array
    {
        $locationId = $locationId ?? config('services.ghl.location_id');
        $accessToken = $accessToken ?? config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return $this->getOpportunitiesByType('sales');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/opportunities/search", [
                'location_id' => $locationId,
                'limit' => 20,
            ]);

            if ($response->successful()) {
                return $response->json('opportunities') ?? [];
            }

            Log::error('GHL API Response Error: '.$response->body());

            return $this->getOpportunitiesByType('sales');

        } catch (\Exception $e) {
            Log::error('GHL API Exception: '.$e->getMessage());

            return $this->getOpportunitiesByType('sales');
        }
    }

    public function getGhlPipelines(): array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/opportunities/pipelines", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                return $response->json('pipelines') ?? [];
            }

            Log::error('GHL Fetch Pipelines Error: '.$response->body());

            return [];
        } catch (\Exception $e) {
            Log::error('GHL Fetch Pipelines Exception: '.$e->getMessage());

            return [];
        }
    }

    public function createOpportunity(array $quoteData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return null;
        }

        try {
            $pipelines = $this->getGhlPipelines();
            $pipelineId = null;
            $stageId = null;

            if (! empty($pipelines)) {
                $firstPipeline = $pipelines[0];
                $pipelineId = $firstPipeline['id'] ?? null;

                if (! empty($firstPipeline['stages'])) {
                    $stageId = $firstPipeline['stages'][0]['id'] ?? null;
                }
            }

            $payload = [
                'locationId' => $locationId,
                'name' => ($quoteData['customer_name'] ?? 'Customer').' - Quote',
                'status' => 'open',
                'monetaryValue' => $quoteData['total_amount'] ?? 0,
            ];

            if ($pipelineId) {
                $payload['pipelineId'] = $pipelineId;
            }
            if ($stageId) {
                $payload['stageId'] = $stageId;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/opportunities/", $payload);

            if ($response->successful()) {
                return $response->json('opportunity');
            }

            Log::error('GHL Create Opportunity Error: '.$response->body());

            return null;

        } catch (\Exception $e) {
            Log::error('GHL Create Opportunity Exception: '.$e->getMessage());

            return null;
        }
    }

    public function updateOpportunity(string $ghlId, array $data): ?array
    {
        $accessToken = config('services.ghl.api_key');
        if (! $accessToken || ! $ghlId) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->put("{$this->baseUrl}/opportunities/{$ghlId}", $data);

            if ($response->successful()) {
                return $response->json('opportunity');
            }

            Log::error('GHL Update Opportunity Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('GHL Update Opportunity Exception: '.$e->getMessage());

            return null;
        }
    }

    public function deleteOpportunity(string $ghlId): bool
    {
        $accessToken = config('services.ghl.api_key');
        if (! $accessToken || ! $ghlId) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->delete("{$this->baseUrl}/opportunities/{$ghlId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('GHL Delete Opportunity Exception: '.$e->getMessage());

            return false;
        }
    }

    public function createContact(array $contactData): ?array
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return null;
        }

        try {
            $payload = array_merge(['locationId' => $locationId], $contactData);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/contacts/", $payload);

            if ($response->successful()) {
                return $response->json('contact');
            }

            Log::error('GHL Create Contact Error: '.$response->body());

            return null;
        } catch (\Exception $e) {
            Log::error('GHL Create Contact Exception: '.$e->getMessage());

            return null;
        }
    }

    public function sendSMS(string $phone, string $message): bool
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/conversations/messages", [
                'type' => 'SMS',
                'locationId' => $locationId,
                'phone' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('GHL SMS Exception: '.$e->getMessage());

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
                        'id' => 'ghl_cnt_00'.$item->id,
                        'name' => $item->name,
                        'email' => strtolower(str_replace(' ', '.', $item->name)).'@example.com',
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

    // --- Sync Methods with Local Database Storage ---

    public function syncOpportunities(): int
    {
        $opportunities = $this->getOpportunities();
        $count = 0;

        foreach ($opportunities as $opp) {
            if (Schema::hasTable('opportunities')) {
                DB::table('opportunities')->updateOrInsert(
                    ['ghl_opportunity_id' => $opp['id'] ?? null],
                    [
                        'name' => $opp['name'] ?? 'Unnamed Opportunity',
                        'stage' => $opp['stage'] ?? 'new',
                        'status' => $opp['status'] ?? 'open',
                        'value' => $opp['monetaryValue'] ?? ($opp['value'] ?? 0),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            $count++;
        }

        return $count;
    }

    public function syncContacts(): int
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/contacts/", [
                'locationId' => $locationId,
                'limit' => 50,
            ]);

            if ($response->successful()) {
                $contacts = $response->json('contacts') ?? [];
                $count = 0;

                foreach ($contacts as $contact) {
                    if (Schema::hasTable('contacts')) {
                        DB::table('contacts')->updateOrInsert(
                            ['email' => $contact['email'] ?? null],
                            [
                                'name' => trim(($contact['firstName'] ?? '').' '.($contact['lastName'] ?? '')),
                                'phone' => $contact['phone'] ?? null,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }
                    $count++;
                }

                return $count;
            }
        } catch (\Exception $e) {
            Log::error('GHL Sync Contacts Exception: '.$e->getMessage());
        }

        return 0;
    }

    public function syncUsers(): int
    {
        $locationId = config('services.ghl.location_id');
        $accessToken = config('services.ghl.api_key');

        if (! $locationId || ! $accessToken) {
            return 0;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/users/", [
                'locationId' => $locationId,
            ]);

            if ($response->successful()) {
                $users = $response->json('users') ?? [];
                $count = 0;

                foreach ($users as $user) {
                    if (Schema::hasTable('users')) {
                        User::updateOrCreate(
                            ['email' => $user['email'] ?? null],
                            [
                                'name' => trim(($user['firstName'] ?? '').' '.($user['lastName'] ?? '')),
                                'phone' => $user['phone'] ?? null,
                                'role' => $user['role'] ?? 'account-user',
                                'password' => bcrypt('password123'),
                            ]
                        );
                        $count++;
                    }
                }

                return $count;
            }
        } catch (\Exception $e) {
            Log::error('GHL Sync Users Exception: '.$e->getMessage());
        }

        return 0;
    }

    public function syncAppointments(): int
    {
        return 0;
    }

    public function syncProducts(): int
    {
        $products = $this->fetchProducts();

        return count($products);
    }
}
