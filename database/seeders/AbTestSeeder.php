<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbTestSeeder extends Seeder
{
    public function run()
    {
        DB::table('ab_tests')->insert([
            [
                'name' => 'Example A/B Test 1',
                'is_active' => true,
                'started_at' => Carbon::now(),
                // 'ended_at' is left null to indicate the test is ongoing
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Example A/B Test 2',
                'is_active' => true,
                'started_at' => Carbon::now(),
                // 'ended_at' is left null to indicate the test is ongoing
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
