<?php

namespace App\Http\Controllers;

use App\Models\SaleRecord;

use App\Http\Resources\MonthlyRecordResource;
use App\Http\Resources\SaleHistoryResource;
use App\Http\Resources\VoucherResource;
use App\Http\Resources\YearlySaleRecordResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRecord;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleRecordController extends Controller
{
    public function saleOpen()
    {
        $setting = Setting::find(1);
        $setting->update(["status" => "open"]);

        return response()->json(["message" => "shop is open"]);
    }

    public function saleClose()
    {
        try {


            DB::beginTransaction();
            $setting = Setting::find(1);
            $setting->update(["status" => "close"]);

            $today = Carbon::today();


            $vouchers = Voucher::whereDate("created_at", $today)->get();
            $total_cash = $vouchers->sum("total");
            $total_tax = $vouchers->sum("tax");
            $total_net_total = $vouchers->sum("net_total");
            $total_vouchers = $vouchers->count();

            SaleRecord::create([
                "total_cash" => $total_cash,
                "total_tax" => $total_tax,
                "total_net_total" => $total_net_total,
                "total_vouchers" => $total_vouchers,
                "user_id" => Auth::id(),
                "status" => "daily"
            ]);
            DB::commit();
            return response()->json(["message" => "shop is close"], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function monthlyClose()
    {
        if (!(request()->has("month") && request()->has("year"))) {
            return response()->json([
                "message" => "month and year are required"
            ]);
        }
        try {
            DB::beginTransaction();
            $setting = Setting::find(1);
            $setting->update(["status" => "close"]);



            $query = SaleRecord::where("status", "daily")
                ->whereMonth("created_at", request()->month)
                ->whereYear("created_at", request()->year);
            $all_records = $query->get();

            $total_cash = $all_records->sum("total_cash");
            $total_tax = $all_records->sum("total_tax");
            $total = $all_records->sum("total_net_total");
            $total_vouchers = $all_records->sum("total_vouchers");

            SaleRecord::insert([
                "total_cash" => $total_cash,
                "total_tax" => $total_tax,
                "total_net_total" => $total,
                "total_vouchers" => $total_vouchers,
                "status" => "monthly",
                "created_at" => Carbon::createFromDate(request()->year,  request()->month, 1)->endOfMonth(),
                "updated_at" => now(),
                "user_id" => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                "message" => "monthly record successfully saved"
            ], 201);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function recent()
    {
        $today = Carbon::today();
        $query = Voucher::whereDate("created_at", $today)->latest("created_at");
        $vouchers = $query->paginate(10)->withQueryString();
        $all_vouchers = $query->get();

        $total_cash = $all_vouchers->sum("total");
        $total_tax = $all_vouchers->sum("tax");
        $total_vouchers = $all_vouchers->count();
        $total = $all_vouchers->sum("net_total");

        return VoucherResource::collection($vouchers)->additional(["total" => [
            "total_voucher" => $total_vouchers,
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total" => $total
        ]]);
    }

    public function daily()
    {
        if (!request()->has("date")) {
            return response()->json([
                "message" => "date is required"
            ]);
        }
        $date = Carbon::createFromFormat("d/m/Y", request()->date);
        $query = Voucher::whereDate("created_at", $date)->latest("created_at");
        $all_records = $query->get();
        $records = $query->paginate(10)->withQueryString();

        $total_cash = $all_records->sum("total");
        $total_tax = $all_records->sum("tax");
        $total = $all_records->sum("net_total");
        $total_vouchers = $all_records->count();

        return VoucherResource::collection($records)->additional(["total" => [
            "total_voucher" => $total_vouchers,
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total" => $total
        ]]);
    }

    public function monthly()
    {
        if (!(request()->has("month") && request()->has("year"))) {
            return response()->json([
                "message" => "month and year are required"
            ]);
        }
        $query = SaleRecord::where("status", "daily")->whereMonth("created_at", request()->month)->whereYear("created_at", request()->year);
        $all_records = $query->get();
        $records = $query->latest("created_at")->paginate(10)->withQueryString();

        $total_cash = $all_records->sum("total_cash");
        $total_tax = $all_records->sum("total_tax");
        $total = $all_records->sum("total_net_total");
        $total_vouchers = $all_records->sum("total_vouchers");

        return MonthlyRecordResource::collection($records)->additional(["total" => [
            "total_voucher" => $total_vouchers,
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total" => $total
        ]]);
    }

    public function yearly()
    {
        if (!request()->has("year")) {
            return response()->json([
                "message" => "month and year are required"
            ]);
        }
        $query = SaleRecord::where("status", "monthly")->whereYear("created_at", request()->year)->latest("created_at");
        $all_records = $query->get();
        $records = $query->paginate(10)->withQueryString();

        $total_cash = $all_records->sum("total_cash");
        $total_tax = $all_records->sum("total_tax");
        $total = $all_records->sum("total_net_total");
        $total_vouchers = $all_records->sum("total_vouchers");

        return YearlySaleRecordResource::collection($records)->additional(["total" => [
            "total_voucher" => $total_vouchers,
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total" => $total
        ]]);
    }

    public function custom()
    {
        if (!request()->has("start") && !request()->has("end")) {
            return response()->json(["message" => "start date and end date are required"], 400);
        }

        $startDate = Carbon::createFromFormat("d/m/Y", request()->start)->subDay(1);
        $endDate = Carbon::createFromFormat("d/m/Y", request()->end);

        $query = Voucher::whereBetween("created_at", [$startDate, $endDate]);
        $all_records = $query->get();
        $vouchers = $query->latest("created_at")->paginate(10)->withQueryString();

        $total_cash = $all_records->sum("total");
        $total_tax = $all_records->sum("tax");
        $total = $all_records->sum("net_total");
        $total_vouchers = $all_records->count();

        return VoucherResource::collection($vouchers)->additional(["total" => [
            "total_voucher" => $total_vouchers,
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total" => $total
        ]]);
    }

    public function saleHistory($productId)
    {
        $sale_history = VoucherRecord::where("product_id", $productId)->paginate(5)->withQueryString();

        return SaleHistoryResource::collection($sale_history);
    }
}
