<?php

namespace Database\Seeders;

use App\Models\Entry;
use Illuminate\Database\Seeder;

class EntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            ['description' => 'AWS Invoice March',       'amount' => 320.00,  'currency' => 'USD', 'transaction_date' => '2026-03-10'],
            ['description' => 'Office Supplies',         'amount' => 154.50,  'currency' => 'MYR', 'transaction_date' => '2026-03-08'],
            ['description' => 'Google Workspace',        'amount' => 84.00,   'currency' => 'USD', 'transaction_date' => '2026-03-05'],
            ['description' => 'Client Dinner',           'amount' => 320.00,  'currency' => 'MYR', 'transaction_date' => '2026-03-01'],
            ['description' => 'Figma Subscription',      'amount' => 45.00,   'currency' => 'USD', 'transaction_date' => '2026-02-28'],
            ['description' => 'Server Hosting',          'amount' => 199.00,  'currency' => 'MYR', 'transaction_date' => '2026-02-20'],
            ['description' => 'Flight to Singapore',     'amount' => 550.00,  'currency' => 'SGD', 'transaction_date' => '2026-02-15'],
        ];

        foreach ($entries as $entry) {
            Entry::create($entry);
        }
    }
}
