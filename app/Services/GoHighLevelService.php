<?php

namespace App\Services;

class GoHighLevelService
{
    public function getOpportunities()
    {
        return [
            [
                'id' => 'ghl_opt_1001',
                'name' => 'Complete Detailing Package - John D.',
                'status' => 'open',
                'value' => 350.00,
                'contact' => [
                    'id' => 'ghl_cnt_001',
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                ]
            ],
            [
                'id' => 'ghl_opt_1002',
                'name' => 'Ceramic Coating Prep - Sarah C.',
                'status' => 'won',
                'value' => 850.00,
                'contact' => [
                    'id' => 'ghl_cnt_002',
                    'name' => 'Sarah Connor',
                    'email' => 'sarah.c@skynet.com',
                ]
            ],
        ];
    }
}