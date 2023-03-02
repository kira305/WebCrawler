<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequestIntervalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $lastRequestTime = $request->session()->get('last_request_time');
        if ($lastRequestTime !== null) {
            $timeInterval = time() - $lastRequestTime;

            if ($timeInterval < 1) {
                dd('Please wait at least 1 second before sending another request.');
                return response()->json(['message' => 'Please wait at least 1 second before sending another request.'], 429);
            }
        }

        $request->session()->put('last_request_time', time());
        return $next($request);
    }
}
