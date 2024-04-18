<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AbTest;
use App\Models\AbTestVariant;
use App\Services\AbTestService;
use Illuminate\Support\Facades\Log;

class AssignAbTestVariant
{

    protected $abTestService;

    public function __construct(AbTestService $abTestService)
    {
        $this->abTestService = $abTestService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Check if the session already has a variant assigned
        if ($request->session()->has('ab_test_variant')) { 
            return $next($request);
        }

        $activeTests = ABTest::getActive();
        // Check if there are active A/B tests
        if ($activeTests->isEmpty()) {
            return $next($request);
        }

        // Sort active tests by their session count
        $sortedTestIds = $activeTests->mapWithKeys(fn($test) => [$test->id => $this->abTestService->getTestSessionCount($test->id)])
        ->sort();

        Log::info('Test counts: ' . json_encode($sortedTestIds->toArray()));

        foreach ($sortedTestIds->keys() as $testId) {
            $variants = AbTestVariant::where('ab_test_id', $testId)->get();
            if ($variants->isEmpty()) {
                continue; // Skip tests without variants
            }
            
            $variantCounts = $this->abTestService->getAllVariantSessionCounts($testId);
            Log::info("Selected Test ID: {$testId}");
            Log::info('Variant counts: ' . json_encode($variantCounts));

            $selectedVariant = $this->selectVariantBasedOnRatio($variants, $variantCounts);

            if ($selectedVariant) {
                $request->session()->put('ab_test', $testId);
                $request->session()->put('ab_test_variant', $selectedVariant->id);
                $this->abTestService->incrementTestSessionCount($testId);
                $this->abTestService->incrementVariantSessionCount($testId, $selectedVariant->id);
                Log::info("Selected Variant ID: {$selectedVariant->id}");
                break; // Break the loop once a variant is assigned
            }
        }

        return $next($request);
    }


    /* selectVariantBasedOnRatio sums up the count of all given variants (under an A/B test) and 
     * selects the variant that is closest to reaching their target targeting_ratio. This is because
     * in most cases the lower targeting_ratio will have the new feature(s) and generally we want that
     * to be tested ASAP to know if there are any issues after deployment
    */
    public function selectVariantBasedOnRatio($variants, $variantCounts)
    {
        $totalTargetingRatio = $variants->sum('targeting_ratio');
        $totalSessions = array_sum($variantCounts);
        $selectedVariant = null;
        $maxDeficit = null;

        foreach ($variants as $variant) {
            $expectedCount = ($variant->targeting_ratio / $totalTargetingRatio) * $totalSessions;
            $actualCount = $variantCounts[$variant->id] ?? 0;
            $deficit = $expectedCount - $actualCount;

            if (is_null($maxDeficit) || $deficit > $maxDeficit) {
                $maxDeficit = $deficit;
                $selectedVariant = $variant;
            }
        }

        return $selectedVariant;
    }

}
