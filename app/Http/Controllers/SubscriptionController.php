<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    //
    /**
     * mohamed mahmoud 24-12-2020 | 1:55pm
     * create subscriotion
     * email,profession,name : requierd
     * other data can be null
     * status 402 for success
     * status 500 for server error
     * Illuminate\Database\QueryException for excepet query error
     * and that should be ignoder in the deploymen.
     *
     */
    function create(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|unique:subscriptions|email:rfc,dns',
            'profession' => 'required',
            'name' => 'required',
        ]);
        $data = [
            'email'=> $request->email,
            'name'=> $request->name,
            'profession'=>$request->profession,
            'country'=> $request->country,
            'city'=> $request->city,
            'address'=>$request->address
        ];
        try{
            Subscription::create($data);
            return response()->json([
            'data' => $data,
            'status'=>402
            ]);
        }catch(\Illuminate\Database\QueryException $error_message_sql) {
            return response()->json([
                'data' => $data,
                'report'=> $error_message_sql->getMessage(),
                'status'=>500
            ]);
        }
    }
}
