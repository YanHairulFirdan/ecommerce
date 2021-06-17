<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Product;
use App\Province;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|integer'
        ]);

        $carts = $this->getCarts();

        if ($carts && array_key_exists($request->product_id, $carts)) {
            $carts[$request->product_id]['qty'] += $request->qty;
        } else {
            $product = Product::find($request->product_id);
            $carts[$request->product_id] = [
                'qty' => $request->qty,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'product_image' => $product->image
            ];
        }

        $cookie = cookie('dw-carts', json_encode($carts), true);


        return redirect()->back()->cookie($cookie);
    }

    public function listCart()
    {
        $carts = json_decode(request()->cookie('dw-carts'), true);
        // dd($carts);
        $subtotal = collect($carts)->sum(function ($cart) {
            return $cart['qty'] * $cart['product_price'];
        });

        return view('ecommerce.cart', compact('carts', 'subtotal'));
    }

    public function updateCart(Request $request)
    {
        $carts = $this->getCarts();

        foreach ($request->product_id as $key => $row) {
            if ($request->qty[$key] == 0) {
                unset($carts[$row]);
            } else {
                $carts[$row]['qty'] += $request->qty[$key];
            }
        }

        $cookie = cookie('dw-carts', json_encode($carts));

        return redirect()->back()->cookie($cookie);
    }

    private function getCarts()
    {
        $carts = json_decode(request()->cookie('dw-carts'), true) ?: [];

        return $carts;
    }

    public function checkout()
    {
        $provinces = Province::orderBy('created_at', 'DESC')->get();

        $carts = $this->getCarts();

        $subtotal = collect($carts)->sum(function ($cart) {
            return $cart['qty'] * $cart['product_price'];
        });

        return view('ecommerce.checkout', compact('provinces', 'carts', 'subtotal'));
    }
}
