<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use mysql_xdevapi\Exception;
use Response;
use Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use App\Support\Services\AddImagesToEntity;


class RegisterController extends Controller
{
    //


    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }


    public function register(Request $request){
        $guard = $request->route()->getName();
        
        try {
            $this->validate($request, [
                'fname'=>'nullable|string|max:255',
                'lname' => 'nullable|string|max:250',
                'email' => $guard == 'user' ? 'required|email|max:255|unique:users,email' : 'required|email|max:255|unique:users,email',
                'password' => 'required|confirmed|min:6|max:255',
             
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'successful' => '0',
                'status'  => '02',
                'error' => 'Invalid data: '.$e
            ], 400);
        }

        $user_input = $request->only(
            'fname', 'lname', 'email', 'password'
        );

        $user_input['password'] = bcrypt($request->get('password'));
        $user_input['active'] = 0;
        $user_input['verification_token'] = Str::random(64);
    
     if ($guard == 'user') {

            $user = new User($user_input);
        }
        
        if ($user->save()) {

            if ($request->hasFile('avatar')) {
                (new AddImagesToEntity($request->avatar, $user, ["width" => 600] ))->execute() ;
            }

            $token = auth('user')->login($user);

            $tokenExpiresAt = \Carbon\Carbon::now()->addMinutes(auth($guard)->factory()->getTTL() * 1)->toDateTimeString();

            return response()->json([
                'successful' => '1',
                'status' => '01',
                'message' => 'Your account has been registered successfully',
                'token_type' => 'Bearer',
                'access_token' => $token,
                'expires_at' => $tokenExpiresAt,
                'user' => $user,
                'type' =>$guard

            ], 200);
        }

        return response()->json([
            'successful' => '0',
            'status'  => '02',
            'error' => 'failed, please try again'
        ], 500);
    }
}