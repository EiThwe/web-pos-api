<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleRecord extends Model
{
    use HasFactory;

    protected $fillable = ["total_cash", "total_tax","total_net_total", "total_vouchers", "status", "user_id"];
}
