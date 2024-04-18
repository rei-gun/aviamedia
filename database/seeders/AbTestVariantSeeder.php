<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbTestVariantSeeder extends Seeder
{
    public function run()
    {
        $abTestId = DB::table('ab_tests')->first()->id;

        $variants = [
            ['name' => 'Variant 1-A', 'targeting_ratio' => 2],
            ['name' => 'Variant 1-B', 'targeting_ratio' => 3],
            ['name' => 'Variant 1-C', 'targeting_ratio' => 5],
        ];

        foreach ($variants as $variant) {
            DB::table('ab_test_variants')->insert([
                'ab_test_id' => $abTestId,
                'name' => $variant['name'],
                'targeting_ratio' => $variant['targeting_ratio'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $abTestId = DB::table('ab_tests')->latest('id')->first()->id;

        $variants = [
            ['name' => 'Variant 2-A', 'targeting_ratio' => 1],
            ['name' => 'Variant 2-B', 'targeting_ratio' => 2],
        ];

        foreach ($variants as $variant) {
            DB::table('ab_test_variants')->insert([
                'ab_test_id' => $abTestId,
                'name' => $variant['name'],
                'targeting_ratio' => $variant['targeting_ratio'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
