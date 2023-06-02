<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\View\View;

class SignUpController extends Controller
{
    public function index(): View
    {
        return view('client.pages.signup');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed|min:6|max:32'
        ], [
            'email.required' => 'Email can not be empty',
            'email.unique' => 'This email is already in used',
            'password.required' => 'Password can not be empty',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password length must be greater than 6',
            'password.max' => 'Password length must be less than 32',
        ]);
        $request = $request->except(['password_confirmation', '_token']);
        $request['password'] = Hash::make($request['password']);
        $user = User::create($request);

        $builder = new \AshAllenDesign\ShortURL\Classes\Builder();
        $shortURLObject = $builder->destinationUrl(route('friend.add.no.confirm', $user->id))->make();
        $user->add_friend_link = $shortURLObject->default_short_url;
        $user->save();
        return redirect()->route('login');
    }
}
