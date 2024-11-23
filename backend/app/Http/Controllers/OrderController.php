<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return Order::all(); // Lấy tất cả đơn hàng
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_code' => 'required|string|unique:orders',
            'customer_name' => 'required|string',
            'address' => 'required|string',
            'total_amount' => 'required|numeric',
            'status' => 'required|string|in:Chưa thanh toán,Đã thanh toán,Đang giao,Đã giao,Trả hàng',
            'shipping_date' => 'required|date',
        ]);

        return Order::create($validated); // Tạo đơn hàng mới
    }

    public function show($id)
    {
        return Order::findOrFail($id); // Lấy thông tin đơn hàng theo ID
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'total_amount' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|in:Chưa thanh toán,Đã thanh toán,Đang giao,Đã giao,Trả hàng',
            'shipping_date' => 'sometimes|required|date',
        ]);

        $order = Order::findOrFail($id);
        $order->update($validated); // Cập nhật thông tin đơn hàng
        return $order;
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete(); // Xóa đơn hàng
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
