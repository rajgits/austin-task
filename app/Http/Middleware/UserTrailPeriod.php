<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class UserTrailPeriod
{
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
}
