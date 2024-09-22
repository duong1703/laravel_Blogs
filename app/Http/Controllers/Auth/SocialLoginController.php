<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use User;
use Auth;
use Hash;
use Str;

class SocialLoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $user = Socialite::driver($provider)->user();
        $existingUser = User::where('email', $user->email)->first();
        // dd($user);
        if ($existingUser) {
            Auth::login($existingUser);
        } else {

            User::create([
                'name' => $user->name,
                'email' => $user->email,
                'password' => Hash::make(Str::random(8)),
                'provider_name' => $provider,
                'provider_id' => $user->id,
                'provider_token' => $user->token
            ]);
        }
        return view('/');
    }
}
