<?php

namespace App\Http\Controllers\Ecommerce;

use App\City;
use App\Customer;
use App\District;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use PhpParser\ErrorHandler\Collecting;

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
        $subtotal = $this->subTotal(collect($carts));

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

    public function getCity()
    {
        $cities = City::where('province_id', request()->province_id)->get();

        return response()->json(['status' => 'success', 'data' => $cities]);
    }

    public function getDistrict()
    {
        $districts = District::where('city_id', request()->city_id)->get();

        return response()->json(['status' => 'success', 'data' => $districts]);
    }

    public function processCheckout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required',
            'email' => 'required|email',
            'customer_address' => 'required|string',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::where('email', $request->email)->first();

            if (!Auth::check() && $customer) {
                return redirect()->back()->with(['error' => 'silahkan login terlebih dahulu']);
            }

            $carts = $this->getCarts();

            $subtotal = $this->subTotal(collect($carts));

            $customer = Customer::create([
                'name' => $request->customer_name,
                'email' => $request->email,
                'phone_number' => $request->customer_phone,
                'address' => $request->customer_address,
                'district_id' => $request->district_id,
                'staus' => false
            ]);

            $order = Order::create([
                'invoice' => Str::random(4) . '-' . time(),
                'customer_id' => $customer->id,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'district_id' => $request->district_id,
                'subtotal' => $subtotal

            ]);

            foreach ($carts as $key => $cart) {
                $product = Product::find($cart['product_id']);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cart['product_id'],
                    'price' => $cart['product_price'],
                    'qty' => $cart['qty'],
                    'weight' => $product->weight,
                ]);
            }
            DB::commit();

            $carts = [];

            $cookie = cookie('dw-carts', json_encode($carts, 2880));

            return redirect(route('front.finish_checkout', $order->invoice))->cookie($cookie);
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with(['error', $th->getMessage()]);
        }
    }

    public function checkoutFinish($invoice)
    {
        $order = Order::where('invoide', $invoice)->first();

        return view('ecommerce.checkout_finish', compact('order'));
    }

    private function subTotal(Collection $collection)
    {
        $subtotal = $collection->sum(function ($cart) {
            return $cart['qty'] * $cart['product_price'];
        });

        return $subtotal;
    }
}
