<?php

namespace App\Http\Controllers\Ecommerce;

use App\Category;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Product;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);

        return view('ecommerce.index', compact('products'));
    }

    public function product()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(12);

        return view('ecommerce.product', compact('products'));
    }

    public function categoryProduct($slug)
    {
        $products = Category::where('slug', $slug)->first()->product()->orderBy('created_at', 'DESC')->paginate(12);

        return view('ecommerce.product', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->first();

        return view('ecommerce.show', compact('product'));
    }

    public function verifyCustomerRegistration($token)
    {
        $customer = Customer::where('activate_token', $token)->first();
        $redirectMessage = ['error' => 'Invalid Verifikasi token'];
        if ($customer) {
            $customer->update([
                'activate_token' => null,
                'status' => 1
            ]);

            $redirectMessage = ['success' => 'Verifikasi berhasil, silahkan login'];
        }

        return redirect(route('customer.login'))->with($redirectMessage);
    }
}
