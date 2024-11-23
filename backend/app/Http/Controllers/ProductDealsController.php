<?php

namespace App\Http\Controllers;

use App\Deal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductDealsController extends Controller
{
    public function hotDeals()
    {
        $now = Carbon::now();
        $endDate = $now->addDays(5); // Thêm 5 ngày vào thời điểm hiện tại

        $hotDeal = Deal::where('starts', '<=', $now)
            ->where('ends', '>=', $endDate)
            ->orderBy('sale', 'desc')
            ->first();

        return response()->json([
            'ends' => $endDate, // Trả về ngày kết thúc của giao dịch hot deal
        ]);
    }
}