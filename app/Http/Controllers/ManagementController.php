<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
// use Response;
use Kreait\Firebase\Auth;
use App\Mail\SendMail;
use App\Models\UserVerifications;

class ManagementController extends Controller
{

    protected $auth;
    public function __construct(Auth  $auth)
    {
        $this->auth = $auth;
    }

    public function verifyEmailCode(Request $request)
    {
        $uuid = $request->uid;
        $user = $this->auth->getUser($uuid);
        if (!$user) {
            return response()->json([
                'message' => "User Not Found",
            ], 404);
        }
        $code = mt_rand(100000, 999999);
        $found = UserVerifications::where('uid', $request->uid)->first();
        if (!$found) {
            $found = new UserVerifications();
            $found->uid = $request->uid;
            $found->code = $code;
        } else {
            $found->code = $code;
        }
        if ($found->save()) {
            Mail::to($user->email)->send(new SendMail($code));
            return response()->json(['message' => 'verification code has been sent to your email', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'something went wrong, try again later'], 500);
        }

        // return response()->json(['message' => 'not saved', 'found' => $found], 200);
    }

    public function validatingCode(Request $request)
    {
        $uid = $request->uid;
        $code = $request->code;

        $found = UserVerifications::where('uid', $uid)->first();

        if (!$found) {
            return response()->json([
                'message' => "No User"
            ], 404);
        }
        if ($found->code === $code) {
            $this->auth->updateUser($uid, ['emailVerified' => true]);
            return response()->json([
                'message' => 'Email has been verified successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'This code is wrong or expired'
            ], 405);
        }
    }
}
