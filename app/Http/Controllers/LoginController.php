<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use Response;

class LoginController extends Controller
{

    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    public function index()
    {
        return view('welcome');
    }

    //Login Function
    public function login(Request $request)
    {

        $guard = $request->route()->getName();

        $request->validate([
            'email' => 'nullable|email|max:250',
            'password' => 'required',
        ]);

        $user = $this->getUser($request);
        // Check if user exist
        if (!$user) {
            return response()->json([
                'successful' => '0',
                'status' => '02',
                'message' => 'invalid email, username or password'
            ], 422);
        }

        $creds = [
            "email" => $user->email,
            'password' => $request->get('password')
        ];

        // try login
        try {
            if (!$token = auth($guard)->attempt($creds, ['exp' => Carbon::now()->addDays(70)->timestamp])) {
                return response()->json([
                    'successful' => '0',
                    'status' => '02',
                    'error'  => 'invalid email or passwords',
                    'token' => auth($guard)->attempt($creds, ['exp' => Carbon::now()->addDays(70)->timestamp])
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'successful' => '0',
                'status' => '02',
                'error' => 'could not create user token, please try again'
            ], 500);
        }

        $tokenExpiresAt = Carbon::now()->addMinutes(auth($guard)->factory()->getTTL())->toDateTimeString();

        return response()->json([
            'successful' => '1',
            'status' => '01',
            'message' => 'Welcome Back',
            'token_type' => 'Bearer',
            'Bearer_token' => $token,
            'expires_at' => $tokenExpiresAt,
            'user' => $user,
            'companies' => $user->companies,
            'stores' => $user->stores,
            'is_designer' => $user->is_designer,
            'type' => $guard
            // 'data' => [
            //     'token_type' => 'Bearer',
            //     'access_token' => $token,
            //     'expires_at' => $tokenExpiresAt,
            //     'id' => $user->id,
            //     'fname' => $user->fname,
            //     'lname' => $user->lname,
            //     'email' => $user->email,
            //     'mobile' => $user->mobile ?? '',
            //     'avatar' => $user->avatar ?? '',

            // ]
        ], 200);
    }


    // get the user
    private function getUser(Request $request)
    {
        $user = null;
        if (!empty($request->email)) {
            $user = User::with('images')->where('email', $request->email)->first();
        } else if (!empty($request->mobile)) {
            $user = User::with('images')->where('mobile', $request->mobile)->first();
        }
        return $user;
    }
}
