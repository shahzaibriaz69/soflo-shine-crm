<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestQuoteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('request_quotes')->truncate();

        DB::table('request_quotes')->insert([
            [
                'source_type' => 'instagram',
                'source_label' => 'Instagram DM',
                'badge_status' => 'new',
                'time_ago' => '12 min ago',
                'name' => 'Ashley Nguyen',
                'phone' => '(305) 555-0147',
                'reference_code' => 'RQ-118',
                'vehicle_title' => '2021 Tesla Model Y',
                'vehicle_type' => 'Crossover / Mid SUV',
                'service_requested' => 'Interior + Exterior Full Detail',
                'guide_price' => 402.00,
                'preferred_day' => 'Sat Aug 22 - morning',
                'notes' => 'Dog rides in the back — lots of hair. Juice spill on the second row.',
                'photos' => json_encode(['Exterior — front 3/4', 'Interior — rear seats', 'Interior — cargo']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_type' => 'website',
                'source_label' => 'Website form',
                'badge_status' => 'new',
                'time_ago' => '1 hr ago',
                'name' => 'Devon Pierce',
                'phone' => '(786) 555-0193',
                'reference_code' => 'RQ-117',
                'vehicle_title' => '2019 Ford F-150',
                'vehicle_type' => 'Full-size SUV / Truck',
                'service_requested' => 'Full Exterior Detail',
                'guide_price' => 293.00,
                'preferred_day' => 'Fri Aug 21 - afternoon',
                'notes' => 'Work truck. Heavy water spots on the hood — asked about ceramic pricing.',
                'photos' => json_encode(['Exterior — driver side', 'Exterior — hood']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_type' => 'tiktok',
                'source_label' => 'TikTok comment',
                'badge_status' => 'new',
                'time_ago' => '3 hrs ago',
                'name' => 'Camila Ortiz',
                'phone' => '(305) 555-0128',
                'reference_code' => 'RQ-116',
                'vehicle_title' => '2023 Honda Civic',
                'vehicle_type' => 'Coupe / Sedan',
                'service_requested' => 'Full Interior Detail',
                'guide_price' => 185.00,
                'preferred_day' => 'Sun Aug 23 - any time',
                'notes' => 'Saw the interior transformation video and wants the same on hers.',
                'photos' => json_encode(['Interior — front', 'Interior — rear']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_type' => 'facebook',
                'source_label' => 'Facebook',
                'badge_status' => 'quoted',
                'time_ago' => 'Yesterday',
                'name' => 'Marcus Webb',
                'phone' => '(954) 555-0166',
                'reference_code' => 'RQ-115',
                'vehicle_title' => '2020 Chevy Tahoe',
                'vehicle_type' => 'Full-size SUV / Truck',
                'service_requested' => 'Express Wash & Shine',
                'guide_price' => 85.00,
                'preferred_day' => 'Mon Aug 24 - morning',
                'notes' => 'Wants it clean before a family trip on Tuesday.',
                'photos' => json_encode(['Exterior — rear 3/4']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}