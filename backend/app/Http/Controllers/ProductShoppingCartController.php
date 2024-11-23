<?php
namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductShoppingCartController extends Controller
{
    public function index(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $cartList = $user->cartItems()
            ->with('stock.product')
            ->orderBy('id', 'desc')
            ->get();

        return $cartList;
    }

    public function store(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($request->localCartList) {
            $cartList = json_decode($request->localCartList, true);
            foreach ($cartList as $cartArrayList) {
                foreach($cartArrayList as $cartItem) {
                    $item = $user->cartItems()
                        ->where('stock_id', $cartItem['stock_id'])
                        ->first();
                    if (!$item) {
                        ShoppingCart::create([
                            'user_id' => $user->id,
                            'stock_id' => $cartItem['stock_id'],
                            'quantity' => $cartItem['quantity']
                        ]);
                    }
                }
            }
        } else {
            $item = $user->cartItems()
                ->where('stock_id', $request->stockId)
                ->first();
            if (!$item) {
                ShoppingCart::create([
                    'user_id' => $user->id,
                    'stock_id' => $request->stockId,
                    'quantity' => $request->quantity
                ]);
            } else {
                $stock = Stock::findOrFail($request->stockId);
                if(($item->quantity + $request->quantity) <= $stock->quantity) {
                    $item->increment('quantity', $request->quantity);
                } else {
                    $item->update(['quantity' => $stock->quantity]);
                }
            }
            return $user->cartItems()->count();
        }
    }

    public function guestCart(Request $request) {
        $cartList = json_decode($request['cartList'], true);
        $data = [];
        $count = 1;
        foreach($cartList as $cartArrayList) {
            foreach($cartArrayList as $cartItem) {
                if($cartItem['quantity'] != null) {
                    $stock = null;
                    if($cartItem['stock_id'] != null) {
                        $stock = Stock::with('product')->where('id', $cartItem['stock_id'])->first();
                    }
                    $data[] = ['id' => $count, 'stock_id' => $cartItem['stock_id'], 'quantity' => $cartItem['quantity'], 'stock' => $stock];
                    $count++;
                }
            }
        }
        return $data;
    }

    public function update(Request $request, $id) {
$cartItem = ShoppingCart::with('stock')->where('id', $id)->get();$stockQty = $cartItem->pluck('stock.quantity')->pop();
        if($request->quantity <= $stockQty && $request->quantity > 0) {
            ShoppingCart::where('id', $id)->update(['quantity' => $request->quantity]);
        }
    }

    public function destroy($id)
    {
        try {
            $cartItem = ShoppingCart::find($id);

            if (!$cartItem) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $cartItem->delete();

            return response()->json(['message' => 'Item deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function cartCount(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        return $user->cartItems()->pluck('stock_id')->toArray();
    }
}
