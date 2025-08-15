<?php

namespace App\Http\Middleware;

use Closure;
use App\Subscription;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        $subscription = Subscription::where('organization_id', auth()->user()->organization_id)->first();

        if ($subscription && $subscription->isActive()) {
            return $next($request);
        } else {
            return response()->json(['error' => 'You don\'t have active subscription'], 403);
        }
        
    }
}
