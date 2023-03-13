

# Payment Trial Period Test Task

## There is two way we can develop this task
- 1. By using Laravel Midleware 
- 2. By using Laravel Controler 


## step 1: Insatal laravel cashier
### composer require laravel/cashier

## Step 2: Run cashier migration 
### php artisan vendor:publish --tag="cashier-migrations" 

## Step 3: Define Midleware 
```php

    /**
     * Handle an incoming request. 
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user
        $user = Auth::user();
         // Check if the user is subscribed to the 'default' plan
        if ($user && $user->subscribed('default')) {
             // Get the subscription for the 'default' plan
            $subscription = $user->subscription('default');
            // Check how many days are left until the subscription ends
            $endsAt = $subscription->ends_at;
            $diffInDays = $endsAt->diffInDays(now());
            // If there are 30 or fewer days left until the subscription ends, extend the subscription by 30 days
            if ($diffInDays <= 30) {
                $subscription->skipTrial()->extend(now()->addDays(30));
            }
        }
        return $next($request);
        
    }
    ```
    ### This logic checks whether the user is a paying customer or on a trial, and extends their access accordingly. If the user is a paying customer, the ends_at property of their subscription is updated to extend their billing schedule by 30 days. If the user is on a trial, the trial_ends_at property is updated to extend their trial period by 30 days.
```
## Step 4: Register the middleware
```php
protected $routeMiddleware = [
    // ...
    'extend.access' => \App\Http\Middleware\ExtendAccess::class,
];
```
### Then depend either you can call this midleware from controler side or from Router side. in this example i used router group to call this
```php
//checking user trial on diferent controller from router group with midleware
Route::group(['middleware'=>'trailcheck'],function(){
    Route::get('dashboard', '\App\Http\Admin\Dashboard@trialTest')->name('dashboard');
    Route::get('payment', \App\Admin\Payment::class)->name('payment');
});
```
#### Note: You must include Billable trait in your userModel than only you can capable to call cahier or bilable functions in your midleware or controller (check user Model).

## Step 5 : Unit test for Midleware with two condition expire and non expire time.
```php
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

```
### In case any clarifications means please pass the commet on Git i will update here.
