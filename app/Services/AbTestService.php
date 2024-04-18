<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\AbTestVariant;

//TODO: rename to AbTestCounterCacheService
class AbTestService
{
    public function getTestSessionCount($testId)
    {
        $cacheKey = "{$testId}_sessions_count";
        return Cache::get($cacheKey, 0); // Default to 0 if the cache key does not exist
    }

    public function incrementTestSessionCount($testId)
    {
        $cacheKey = "{$testId}_sessions_count";

        // Ensure the cache key exists and increment the count
        Cache::remember($cacheKey, now()->addDay(), function () {
            return 0; // Initialize with 0 if does not exist
        });
        Cache::increment($cacheKey);
    }

    public function getAllVariantSessionCounts($testId)
    {
        $variantCounts = [];
        $variants = AbTestVariant::where('ab_test_id', $testId)->get(); // Fetch all variants for the given testId

        foreach ($variants as $variant) {
            //TODO: switch to Redis cache and use wildcard instead of accessing DB to get variant IDs
            $cacheKey = "{$testId}_{$variant->id}_variant_sessions_count";
            $variantCounts[$variant->id] = Cache::get($cacheKey, 0); // Default to 0 if not found
        }
        return $variantCounts;
    }


    public function incrementVariantSessionCount($testId, $variantId)
    {
        $cacheKey = "{$testId}_{$variantId}_variant_sessions_count";

        Cache::remember($cacheKey, now()->addDay(), function () {
            return 0;
        });
        Cache::increment($cacheKey);
    }
}
