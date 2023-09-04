<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SaleRecord;
use App\Models\Voucher;
use App\Models\VoucherRecord;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($m = 1; $m <= 8; $m++) {
            $startDate = Carbon::createFromDate(2023, $m, 1);
            $endDate = Carbon::createFromDate(2023, $m, 1)->endOfMonth();

            $numberOfDays = $startDate->diffInDays($endDate) + 1;
            $monthly_total_cash = 0;
            $monthly_total_tax = 0;
            $monthly_total_vouchers = 0;

            for ($d = 1; $d <= $numberOfDays; $d++) {

                for ($i = 0; $i < 10; $i++) {
                    $voucher_records = [];
                    for ($j = 0; $j < 3; $j++) {
                        $productId = rand(1, 10);
                        $product = Product::find($productId);

                        $quantity = rand(1, 10);

                        $voucher_records[] = [
                            "product_id" => $productId,
                            "price" => $product->sale_price,
                            "quantity" => $quantity,
                            "cost" => $product->sale_price * $quantity,
                        ];
                    };

                    $date = Carbon::create(2023, $m, $d)->setTime(rand(9, 16), rand(0, 59), rand(0, 59));
                    $total = 0;
                    foreach ($voucher_records as $record) {
                        $total += $record["quantity"] * $record["price"];
                    };
                    $tax = $total * 0.05;
                    $net_total = $total + $tax;
                    $voucher = [
                        "customer" => fake()->name(),
                        "phone" => fake()->phoneNumber(),
                        "voucher_number" => strtolower(Str::random(10)),
                        "total" => $total,
                        "tax" => $tax,
                        "net_total" => $net_total,
                        "user_id" => rand(1, 10),
                        "created_at" => $date,
                        "updated_at" => $date,
                    ];
                    $vouchers[] = $voucher;

                    $store_voucher = Voucher::create($voucher);

                    $records = [];
                    foreach ($voucher_records as $record) {
                        $records[] = [
                            "voucher_id" => $store_voucher->id,
                            "product_id" => $record["product_id"],
                            "price" => $record["price"],
                            "quantity" => $record["quantity"],
                            "cost" => $record["cost"],
                            "created_at" => $date,
                            "updated_at" => $date
                        ];
                    }

                    VoucherRecord::insert($records);
                }

                $total_cash = array_reduce($vouchers, fn ($pv, $cv) => $pv + $cv["net_total"], 0);
                $total_tax = array_reduce($vouchers, fn ($pv, $cv) => $pv + $cv["tax"], 0);
                $total_vouchers = 10;

                $monthly_total_cash += $total_cash;
                $monthly_total_tax += $total_tax;
                $monthly_total_vouchers += $total_vouchers;

                SaleRecord::insert([
                    "total_cash" => $total_cash,
                    "total_tax" => $total_tax,
                    "total_net_total" => $total_cash + $total_tax,
                    "total_vouchers" => $total_vouchers,
                    "status" => "daily",
                    "user_id" => rand(1, 10),
                    "created_at" => $date,
                    "updated_at" => $date,
                ]);

                if ($d == $numberOfDays) {
                    SaleRecord::insert([
                        "total_cash" => $monthly_total_cash,
                        "total_tax" => $monthly_total_tax,
                        "total_net_total" => $monthly_total_cash + $monthly_total_tax,
                        "total_vouchers" => $monthly_total_vouchers,
                        "status" => "monthly",
                        "user_id" => rand(1, 10),
                        "created_at" => $date,
                        "updated_at" => $date,
                    ]);
                }
            }
        }

        for ($m = 1; $m <= 12; $m++) {
            $startDate = Carbon::createFromDate(2022, $m, 1);
            $endDate = Carbon::createFromDate(2022, $m, 1)->endOfMonth();

            $numberOfDays = $startDate->diffInDays($endDate) + 1;
            $monthly_total_cash = 0;
            $monthly_total_tax = 0;
            $monthly_total_vouchers = 0;

            for ($d = 1; $d <= $numberOfDays; $d++) {

                for ($i = 0; $i < 10; $i++) {
                    $voucher_records = [];
                    for ($j = 0; $j < 3; $j++) {
                        $productId = rand(1, 10);
                        $product = Product::find($productId);

                        $quantity = rand(1, 10);

                        $voucher_records[] = [
                            "product_id" => $productId,
                            "price" => $product->sale_price,
                            "quantity" => $quantity,
                            "cost" => $product->sale_price * $quantity,
                        ];
                    };

                    $date = Carbon::create(2022, $m, $d)->setTime(rand(9, 16), rand(0, 59), rand(0, 59));
                    $total = 0;
                    foreach ($voucher_records as $record) {
                        $total += $record["quantity"] * $record["price"];
                    };
                    $tax = $total * 0.05;
                    $net_total = $total + $tax;
                    $voucher = [
                        "customer" => fake()->name(),
                        "phone" => fake()->phoneNumber(),
                        "voucher_number" => strtolower(Str::random(10)),
                        "total" => $total,
                        "tax" => $tax,
                        "net_total" => $net_total,
                        "user_id" => rand(1, 10),
                        "created_at" => $date,
                        "updated_at" => $date,
                    ];
                    $vouchers[] = $voucher;

                    $store_voucher = Voucher::create($voucher);

                    $records = [];
                    foreach ($voucher_records as $record) {
                        $records[] = [
                            "voucher_id" => $store_voucher->id,
                            "product_id" => $record["product_id"],
                            "price" => $record["price"],
                            "quantity" => $record["quantity"],
                            "cost" => $record["cost"],
                            "created_at" => $date,
                            "updated_at" => $date
                        ];
                    }

                    VoucherRecord::insert($records);
                }

                $total_cash = array_reduce($vouchers, fn ($pv, $cv) => $pv + $cv["net_total"], 0);
                $total_tax = array_reduce($vouchers, fn ($pv, $cv) => $pv + $cv["tax"], 0);
                $total_vouchers = 10;

                $monthly_total_cash += $total_cash;
                $monthly_total_tax += $total_tax;
                $monthly_total_vouchers += $total_vouchers;

                SaleRecord::insert([
                    "total_cash" => $total_cash,
                    "total_tax" => $total_tax,
                    "total_net_total" => $total_cash  + $total_tax,
                    "total_vouchers" => $total_vouchers,
                    "status" => "daily",
                    "user_id" => rand(1, 10),
                    "created_at" => $date,
                    "updated_at" => $date,
                ]);

                if ($d == $numberOfDays) {
                    SaleRecord::insert([
                        "total_cash" => $monthly_total_cash,
                        "total_tax" => $monthly_total_tax,
                        "total_net_total" => $monthly_total_cash + $monthly_total_tax,
                        "total_vouchers" => $monthly_total_vouchers,
                        "status" => "monthly",
                        "user_id" => rand(1, 10),
                        "created_at" => $date,
                        "updated_at" => $date,
                    ]);
                }
            }
        }
    }
}
