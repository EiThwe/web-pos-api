<?php

namespace App\Http\Middleware;

use App\Models\SaleRecord;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTodayRecordExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $today = Carbon::today();
        $todaySaleRecord = SaleRecord::whereDate("created_at", $today)->first();
        if ($todaySaleRecord) {
            return response()->json(["message" => "today sale record has already existed"], 400);
        }

        return $next($request);
    }
}
