<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Socialite;
use Exception;
use App\Models\User;

class SocialController extends Controller
{
    //
    public function redirectFacebook($provider)
    {
        //dd($provider);
        return Socialite::driver($provider)->redirect();
    }


    public function runCallback($provider)

    {
        try {

            $user = Socialite::driver($provider)->stateless()->user();

            $finduser = User::where('facebook_user_id', $user->id)->first();
            if ($finduser) {
                return $finduser;
                return response()->json([
                    'message' => 'User Exist'
                ], 200);
            } else {
                // $newUser = User::create([
                //     'name' => $user->name,
                //     'email' => $user->email,
                //     'facebook_user_id' => $user->id,
                //     'password' => encrypt('123456dummy')
                // ]);
                return response()->json([
                    'message' => 'No Such User'
                ], 405);
            }
        } catch (Exception $e) {
            dd('dfdfdfdfd');
            dd($e->getMessage());
        }
    }
}
