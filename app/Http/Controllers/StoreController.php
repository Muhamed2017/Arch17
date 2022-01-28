<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Follower;
use App\Models\Store;

class StoreController extends Controller
{
    //



    private function isOwner($id)
    {
        return in_array($id, array_column(current((array)auth()->user()->stores), 'id'));
    }

    public function follow(Request $request)
    {
        if (!$this->isOwner($request->store_id)) {
            $store = Store::find($request->store_id);
            $check_user_follower = in_array(auth()->user()->id,  array_column(current((array) $store->followers), 'user_id'));
            if ($check_user_follower) {
                $store->followers()->where(['user_id' => auth()->user()->id])->delete();
            } else {
                $store->followers()->create(['user_id' => auth()->user()->id]);
            }
            return response()->json(
                array(
                    'data' => Store::find($request->store_id)->followers
                )
            );
        } else {
            return response()->json(
                array(
                    'message' => 'you are owner you can not follow you self'
                )
            );
        }
    }


    public function brandcover(Request $request)
    {

        $this->validate($request, [
            'brand_cover' => "nullable|mimes:jpeg,jpg,png|between:1,5000",
            'store_id' => 'nullable|string|max:250',
        ]);


        $store = Store::find($request->store_id);
        $store->cover = $request->brand_cover->storeOnCloudinary()->getSecurePath();

        if ($store->save()) {
            return response()->json([
                'brand' => $store,
                'message' => 'Brand Cover has been updated'
            ], 200);
        }
    }
}
