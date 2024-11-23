<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Abate; // Gọi model Abate
use Illuminate\Support\Facades\Auth; // Gọi Auth để lấy thông tin người dùng

class AbateController extends Controller
{
    /**
     * Lưu đơn hàng
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'products' => 'required|array',
            'products.*.name' => 'required|string|max:255',
            'products.*.quantity' => 'required|integer|min:1',
            'totalMoney' => 'required|numeric|min:0',
            'provinces' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'wards' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Lưu dữ liệu vào bảng abate
        $abate = new Abate();
        $abate->name = $request->name;
        $abate->phone = $request->phone;
        $abate->email = $request->email;
        $abate->products = json_encode($request->products); // Chuyển mảng products thành JSON để lưu
        $abate->totalMoney = $request->totalMoney;
        $abate->provinces = $request->provinces;
        $abate->district = $request->district;
        $abate->wards = $request->wards;
        $abate->address = $request->address;

        // Lưu user_id (đảm bảo người dùng đã đăng nhập)
        $abate->user_id = Auth::id(); // Lưu id của người dùng hiện tại

        try {
            $abate->save(); // Cố gắng lưu dữ liệu
            return response()->json(['message' => 'Đơn hàng đã được lưu thành công!'], 200);
        } catch (\Exception $e) {
            // Ghi lại lỗi và trả về phản hồi lỗi 500
            \Log::error('Lỗi lưu đơn hàng: '.$e->getMessage());
            return response()->json(['message' => 'Lỗi lưu đơn hàng', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy tất cả đơn hàng của người dùng hiện tại
     */
    public function getAll()
    {
        // Lấy tất cả đơn hàng của người dùng hiện tại
        return Abate::where('user_id', Auth::id())->get();
    }

    public function getAbateById($id)
    {
        // Tìm đơn hàng theo id
        $abate = Abate::where('id', $id)->where('user_id', Auth::id())->first();

        // Kiểm tra xem có tìm thấy đơn hàng hay không
        if ($abate) {
            return response()->json($abate, 200); // Trả về thông tin đơn hàng
        } else {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404); // Trả về thông báo lỗi nếu không tìm thấy
        }
    }

    public function delete($id)
    {
        // Tìm đơn hàng theo id và user_id
        $abate = Abate::where('id', $id)->where('user_id', Auth::id())->first();

        if ($abate) {
            $abate->delete();  // Xóa bản ghi nếu tìm thấy
            return response()->json(['message' => 'Xóa thành công!'], 200);
        } else {
            return response()->json(['message' => 'Bản ghi không tồn tại!'], 404);
        }
    }
}
