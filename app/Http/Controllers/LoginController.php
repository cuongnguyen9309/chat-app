<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function index(): View
    {
        return view('client.pages.login');
    }

    public function attempt(Request $request)
    {
        $request->validate([
            'email' => 'email|required',
            'password' => 'required|min:6|max:32'
        ], [
            'email.required' => 'Email can not be empty',
            'password.required' => 'Password can not be empty',
            'password.min' => 'Password length must be greater than 6',
            'password.max' => 'Password length must be less than 32',
        ]);
        $credential = $request->except('_token');

        $bool = auth()->attempt($credential);
        if ($bool) {
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.home');
            }
            return redirect()->route('home');
        } else {
            return redirect()->route('login')->with(['message' => 'Login failed']);
        }
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }
}
