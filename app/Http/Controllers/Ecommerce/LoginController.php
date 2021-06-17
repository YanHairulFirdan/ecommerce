<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function loginForm()
    {
        return view('ecommerce.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,id',
            'password' => 'required|string'
        ]);


        $auth = $request->except('_token');

        $auth['status'] = 1;

        if (Auth::guard('customer')->attempt($auth)) {
            return redirect()->intended('customer.dashboard');
        }

        return redirect()->back()->with(['error' => 'Email/password salah']);
    }

    public function dashboard()
    {
        return view('ecommerce.dashboard');
    }
}
