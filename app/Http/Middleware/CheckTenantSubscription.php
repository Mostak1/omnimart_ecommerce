<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    /**
     * Handle an incoming request for tenant store.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (function_exists('tenant') && $tenant = tenant()) {
            // 1. Check if store is suspended by central super admin
            if ($tenant->isSuspended()) {
                abort(403, "এই দোকানটি সাময়িকভাবে স্থগিত করা হয়েছে। বিস্তারিত জানতে কেন্দ্রীয় কাস্টমার সাপোর্টে যোগাযোগ করুন। (Store ID: {$tenant->id})");
            }

            // 2. Check if store subscription has expired
            if ($tenant->isExpired()) {
                // If it's a mutation/post request in admin, require renewal
                if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'দোকানের সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে। দয়া করে প্যাকেজ নবায়ন করুন।',
                        ], 403);
                    }
                }

                // Share alert with view
                view()->share('subscription_expired_notice', true);
            }
        }

        return $next($request);
    }
}
