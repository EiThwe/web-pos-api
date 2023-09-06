<?php

namespace App\Http\Middleware;

use App\Models\SaleRecord;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\isEmpty;

class MonthlyCloseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMonthlyClose =  SaleRecord::where("status", "monthly")
            ->whereMonth("created_at", request()->month)
            ->whereYear("created_at", request()->year)->first();

        if ($isMonthlyClose) {
            return response()->json(["message" => "monthly record has already existed"]);
        }

        return $next($request);
    }
}
