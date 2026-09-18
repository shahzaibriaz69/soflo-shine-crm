<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\GoHighLevelService;

class SyncLocalToGhl extends Command
{
    protected $signature = 'ghl:sync-local-to-ghl';
    protected $description = 'Push all local opportunities/quotes to GoHighLevel';

    public function handle(GoHighLevelService $ghlService)
    {
        $this->info('Starting sync of local data to GoHighLevel...');

        // 1. Local opportunities fetch karein
        $opportunities = DB::table('opportunities')->get();
        $isFromQuotes = false;

        // 2. Agar opportunities table khali hai, toh quotes table se data utha lein
        if ($opportunities->isEmpty() && Schema::hasTable('quotes')) {
            $opportunities = DB::table('quotes')->get();
            $isFromQuotes = true;
        }

        if ($opportunities->isEmpty()) {
            $this->warn('No records found in opportunities or quotes tables!');
            return;
        }

        $bar = $this->output->createProgressBar(count($opportunities));
        $bar->start();

        foreach ($opportunities as $opp) {
            // Field mapping based on table source
            $customerName = $opp->name ?? $opp->customer_name ?? 'Customer';
            $value = $opp->value ?? $opp->total_estimate ?? 0;

            // GHL par opportunity create karein (ye service khud pipeline/stage dhund legi)
            $ghlResponse = $ghlService->createOpportunity([
                'customer_name' => $customerName,
                'total_amount' => $value,
            ]);

            // Agar GHL se successfully ID mil jaye, toh local record update kar dein
            if (!empty($ghlResponse['id'])) {
                if (!$isFromQuotes && Schema::hasColumn('opportunities', 'ghl_opportunity_id')) {
                    DB::table('opportunities')
                        ->where('id', $opp->id)
                        ->update(['ghl_opportunity_id' => $ghlResponse['id']]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll local records successfully pushed to GoHighLevel! 🚀");
    }
}