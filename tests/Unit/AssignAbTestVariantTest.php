<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\AbTestVariant;
use App\Http\Middleware\AssignAbTestVariant;
use App\Services\AbTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Illuminate\Support\Collection;

uses(TestCase::class, RefreshDatabase::class);

test('test session AssignAbTestVariant->handle', function () {
    $abTestVariant = AbTestVariant::factory()->create();

    // Mock Cache facade methods
    // Cache::shouldReceive('remember')
    //     ->once()
    //     ->withArgs(fn($key, $ttl, $callback) => $callback() === 0) // Ensure the initial value is 0
    //     ->andReturn(0);

    Cache::shouldReceive('increment')
        ->with("{$abTestVariant->ab_test_id}_sessions_count") // Ensure the test session count is incremented
        ->andReturn(1);

    Cache::shouldReceive('increment')
        ->with("{$abTestVariant->ab_test_id}_{$abTestVariant->id}_variant_sessions_count") // Ensure the variant session count is incremented
        ->andReturn(1);

    // Mock AbTestService
    $abTestServiceMock = mock(AbTestService::class)
        ->shouldReceive('incrementTestSessionCount')
        ->once()
        ->with($abTestVariant->ab_test_id)
        ->getMock();

    $abTestServiceMock->shouldReceive('incrementVariantSessionCount')
        ->once()
        ->with($abTestVariant->ab_test_id, $abTestVariant->id)
        ->getMock();

    $abTestServiceMock->shouldReceive('getTestSessionCount')
        ->once()
        ->with($abTestVariant->ab_test_id)
        ->andReturn(0); 

    $abTestServiceMock->shouldReceive('getAllVariantSessionCounts')
        ->once()
        ->with($abTestVariant->ab_test_id)
        ->andReturn([$abTestVariant->id => 0]); 

    /**
     * @var \App\Services\AbTestService|\Mockery\LegacyMockInterface|\Mockery\MockInterface $abTestServiceMock
     */
    $middleware = new AssignAbTestVariant($abTestServiceMock);

    $request = Request::create('/', 'GET');
    $next = fn() => response()->noContent(200);
    $session = Session::getFacadeRoot()->driver();
    $request->setLaravelSession($session);
    $response = $middleware->handle($request, $next);

    // Assertions
    expect($response->getStatusCode())->toBe(200); // Assert response is 200 OK
    expect(session('ab_test'))->toBe($abTestVariant->ab_test_id); // Assert 'ab_test' session variable is set
    expect(session('ab_test_variant'))->toBe($abTestVariant->id); // Assert 'ab_test_variant' session variable is set

    //TODO: assert the cache values were incremented by using a real cache driver
});



test('test selectVariantBasedOnRatio', function () {
    $abTestServiceMock = mock(AbTestService::class);
    $middleware = new AssignAbTestVariant($abTestServiceMock);

    $variants = new Collection([
        (object) ['id' => 1, 'targeting_ratio' => 1],
        (object) ['id' => 2, 'targeting_ratio' => 2],
    ]);

    $variantCounts = [
        1 => 0, 
        2 => 1, 
    ];
 
    $selectedVariant = $middleware->selectVariantBasedOnRatio($variants, $variantCounts);

    expect($selectedVariant->id)->toBe(1); // Expecting variant ID 1 to be selected due to its targeting ratio and session count
});