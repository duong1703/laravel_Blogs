<?php

namespace App\Http\Controllers\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Str;

use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }


    public function callback($provider)
    {

        try {
            $SocialUser = Socialite::driver($provider)->stateless()->user();
            if (User::where('email', $SocialUser->getEmail())->exists()) {
                return redirect('/login')->withErrors(['email' => 'This email uses different method login']);
            }

            $user = User::where([
                'provider_id' => $SocialUser->id,
                'provider' => $provider,
            ])->first();

            if (!$user) {
                $password = Str::random(9);
                $user = User::create([
                    'name' => $SocialUser->name,
                    'email' => $SocialUser->email,
                    'username' => User::generateUsername($SocialUser->getNickname()),
                    'provider' => $provider,
                    'provider_id' => $SocialUser->getId(),
                    'provider_token' => $SocialUser->token,
                    'password' => $password,
                    
                ]);
               
                $user->sendEmailVerificationNotification();
            }

            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $exeption) {
            return redirect('/login');
        }
    }

}
