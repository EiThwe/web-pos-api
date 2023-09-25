<?php

namespace App\Http\Controllers;

use App\Models\SaleRecord;
use App\Http\Requests\StoreSaleRecordRequest;
use App\Http\Requests\UpdateSaleRecordRequest;
use App\Http\Resources\MonthlyRecordResource;
use App\Http\Resources\TodaySaleOverviewResource;
use App\Http\Resources\VoucherResource;
use App\Http\Resources\YearlySaleRecordResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRecord;
use Carbon\Carbon;
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
            "user_id" => Auth::id()
        ]);

        return response()->json(["message" => "shop is close"], 200);
    }

    public function monthlyClose()
    {
        if (!(request()->has("month") && request()->has("year"))) {
            return response()->json([
                "message" => "month and year are required"
            ]);
        }
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

        return response()->json([
            "message" => "monthly record successfully saved"
        ], 201);
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

    public function todaySaleOverview()
    {
        $today = Carbon::today();
        $vouchers = Voucher::whereDate("created_at", $today)->orderBy("net_total", "desc")->get();

        // dd($vouchers);

        $total_amount = $vouchers->sum("net_total");

        $vouchers = json_decode($vouchers, true);


        $top_3_vouchers = array_slice($vouchers, 0, 3);

        $top_3_vouchers = array_map(function ($voucher) use ($total_amount) {
            return [
                "voucher_number" => $voucher["voucher_number"],
                "net_total" => $voucher["net_total"],
                "percentage" => round($voucher["net_total"] / $total_amount * 100, 1) . "%"
            ];
        }, $top_3_vouchers);

        return response()->json([
            "total_amount" => round($total_amount, 2),
            "vouchers" => $top_3_vouchers
        ]);
    }

    public function saleOverview($type)
    {
        $currentDate = Carbon::now();
        $previousDate = '';
        $status = "daily";

        if ($type == "weekly") {
            $previousDate = Carbon::now()->subDays(7);
        } else if ($type == "monthly") {
            $previousDate = Carbon::now()->subDays(30);
        } else if ($type == "yearly") {
            $previousDate = Carbon::now()->subDays(365);
            $status = "monthly";
        } else {
            return response()->json(["message" => "weekly or monthly or yearly is required"]);
        }

        $query = SaleRecord::whereBetween("created_at", [$previousDate, $currentDate])->where("status", $status);
        $query2 = SaleRecord::whereBetween("created_at", [$previousDate, $currentDate])->where("status", $status);

        $average = $query->avg("total_net_total");

        $records = $query->select("total_net_total", "created_at")->get();

        $max = $query->where('total_net_total', $query->max('total_net_total'))->select("total_net_total", "created_at")->first();

        $min = $query2->where('total_net_total', $query2->min('total_net_total'))->select("total_net_total", "created_at")->first();


        $product_sales = VoucherRecord::whereBetween("created_at", [$previousDate, $currentDate])
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderBy("total_quantity", "desc")
            ->limit(5)
            ->get();
        $product_sales = json_decode($product_sales, true);


        $product_sales = array_map(function ($product_sale) {
            $product = Product::find($product_sale["product_id"]);
            return [
                "product_name" => $product->name,
                "brand" => $product->brand->name,
                "sale_price" => $product->sale_price
            ];
        }, $product_sales);

        $product_quantity = VoucherRecord::whereBetween("created_at", [$previousDate, $currentDate])
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->get();

        for ($i = 0; $i < count($product_quantity); $i++) {
            $product = Product::find($product_quantity[$i]["product_id"]);
            $product_quantity[$i]["brand_id"] = $product->brand_id;
            $product_quantity[$i]["brand_name"] = Brand::find($product->brand_id)->name;
        };


        $product_quantity = json_decode($product_quantity, true);

        $best_seller_brands = array();

        foreach ($product_quantity as $item) {
            $isExist = array_search($item["brand_id"], array_column($best_seller_brands, "brand_id"));

            if (is_numeric($isExist)) {
                $best_seller_brands[$isExist]["total_quantity"] += $item["total_quantity"];
            } else {
                array_push($best_seller_brands, [
                    "brand_id" => $item["brand_id"],
                    "brand_name" => $item["brand_name"],
                    "total_quantity" => $item["total_quantity"]
                ]);
            }
        }

        usort($best_seller_brands, fn ($a, $b) => $b['total_quantity'] - $a['total_quantity']);

        $best_seller_brands = array_slice($best_seller_brands, 0, 5);

        $totalQuantity = 0;

        foreach ($best_seller_brands as $item) {
            $totalQuantity += $item['total_quantity'];
        }

        $best_seller_brands = array_map(function ($brands) use ($totalQuantity) {
            $brands["percentage"] = round($brands["total_quantity"] / $totalQuantity * 100, 1) . "%";

            return $brands;
        }, $best_seller_brands);

        return response()->json([
            "average" => $average,
            "max" => $max,
            "min" => $min,
            "sale_records" => $records,
            "product_sales" => $product_sales,
            "brand_sales" => $best_seller_brands
        ]);
    }

    public function dashboardOverview($type)
    {
        $currentDate = Carbon::now();
        $previousDate = '';
        $status = "daily";

        if ($type == "weekly") {
            $previousDate = Carbon::now()->subDays(7);
        } else if ($type == "monthly") {
            $previousDate = Carbon::now()->subDays(30);
        } else if ($type == "yearly") {
            $previousDate = Carbon::now()->subDays(365);
            $status = "monthly";
        } else {
            return response()->json(["message" => "weekly or monthly or yearly is required"]);
        }

        $query = SaleRecord::whereBetween("created_at", [$previousDate, $currentDate])->where("status", $status);

        $records = $query->select("total_net_total", "created_at")->get();
        $products = Product::where("total_stock", ">=", 0)->get();
        $total_stocks = $products->sum("total_stock");
        $total_staff = User::where("id", "!=", 1)->count();

        $voucher_records = VoucherRecord::whereBetween("created_at", [$previousDate, $currentDate])
            ->select("product_id", DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(cost) as total_cost'))
            ->groupBy("product_id")
            ->get();

        $total_income = $voucher_records->sum("total_cost");
        $total_expense = 0;

        $voucher_records = json_decode($voucher_records, true);
        foreach ($voucher_records as $record) {
            $product = Product::find($record["product_id"]);
            $total_expense += $product->actual_price * $record["total_quantity"];
        }

        $total_profit = $total_income - $total_expense;

        return response()->json([
            "total_stocks" => $total_stocks,
            "total_staff" => $total_staff,
            "sale_records" => $records,
            "stats" => [
                "total_income" => $total_income,
                "total_expense" => $total_expense,
                "total_profit" => $total_profit
            ]
        ]);
    }
}
