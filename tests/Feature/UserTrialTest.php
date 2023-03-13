<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ExtendTrialIfNearExpiration;

class UserTrialTest extends TestCase
{
    public function testMiddlewareExpire()
    {
        // Create a mock authenticated user with a subscription that ends in 29 days
        $user = new stdClass;
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $subscription = new stdClass;
        $subscription->ends_at = now()->addDays(29);
        $user->shouldReceive('subscribed')->with('default')->andReturn(true);
        $user->shouldReceive('subscription')->with('default')->andReturn($subscription);
        Auth::shouldReceive('user')->andReturn($user);

        // Create a mock request and response
        $request = new Request;
        $response = new Response;

        // Create an instance of the middleware and call the handle method
        $middleware = new ExtendTrialIfNearExpiration;
        $result = $middleware->handle($request, function ($req) use ($response) {
            return $response;
        });

        // Assert that the subscription was extended by 30 days
        $this->assertEquals(now()->addDays(59)->toDateString(), $subscription->ends_at->toDateString());

        // Assert that the request was passed through to the next middleware
        $this->assertEquals($response, $result);
    }

    public function testMiddlewareNotExpire()
    {
        // Create a mock authenticated user with a subscription that ends in 31 days
        $user = new stdClass;
        $user->shouldReceive('subscribed')->with('default')->andReturn(true);
        $subscription = new stdClass;
        $subscription->ends_at = now()->addDays(31);
        $user->shouldReceive('subscription')->with('default')->andReturn($subscription);
        Auth::shouldReceive('user')->andReturn($user);

        // Create a mock request and response
        $request = new Request;
        $response = new Response;

        // Create an instance of the middleware and call the handle method
        $middleware = new ExtendTrialIfNearExpiration;
        $result = $middleware->handle($request, function ($req) use ($response) {
            return $response;
        });

        // Assert that the subscription was not extended
        $this->assertEquals(now()->addDays(31)->toDateString(), $subscription->ends_at->toDateString());

        // Assert that the request was passed through to the next middleware
        $this->assertEquals($response, $result);
    }

}
