<?php

namespace App\Http\Controllers;

use App\Models\BusinessAccount;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    //creatiing a bussiness account api
    public function CreateBusinessAccount(Request $request)
    {
        $user_id = auth('user')->user()->id;

        // check if the user exist
        $user = User::findOrFail($user_id);
        if (!$user) {
            return response()->json([
                'message' => 'User dosent Exist'
            ], 404);
        }

        // check if the user has business account
        if ($user->businessAccount) {
            return response()->json([
                'message' => 'You already have created besiness account',
                // 'sss'=> $user->businessAccount
            ], 405);
        }

        //valiating the request
    try {
            $this->validate($request, [
                'proffession_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:business_account',
                'phone' => 'required|string|max:250',
                'passcode' => 'required|confirmed|min:6|max:255',

            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'successful' => '0',
                'status'  => '02',
                'error' => 'Invalid data: ' . $e,
                'error_msg'=>$e
            ], 400);
        }

        // creating the business account

        $bussiness = new BusinessAccount();
        $bussiness->user_id = $user_id;
        $bussiness->proffession_name = $request->proffession_name;
        $bussiness->phone = $request->phone;
        $bussiness->email = $request->email;
        $bussiness->passcode= bcrypt($request->get('passcode'));

        if ($bussiness->save()) {
            return response()->json([
                'message' => 'Your besiness account has been successfully created!',
                'Account' => $bussiness
            ], 201);
        }
        else{
            return response()->json([
                'status'=>'02',
                'message'=>'Error occurs, try agian !'
            ], 500);
        }
    }
    //end of create business account api
}
