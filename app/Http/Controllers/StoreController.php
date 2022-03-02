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


    public function brandCover(Request $request)
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
    public function brandLogo(Request $request, $id)
    {
        $this->validate($request, [
            'logo' => "nullable|mimes:jpeg,jpg,png|between:1,5000",
        ]);

        $store = Store::find($id);
        if ($request->hasFile('logo')) {
            $store->logo = $request->logo->storeOnCloudinary()->getSecurePath();
        }

        if ($store->save()) {
            return response()->json([
                'brand' => $store,
                'message' => 'Brand Cover has been updated'
            ], 200);
        }
    }


    public function updateBrand(Request $request, $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json([
                'message' => 'Brand Not Found!',
            ], 404);
        }

        $this->validate($request, [
            'name' => 'nullable|string|max:250',
            'product_types' => 'nullable|array',
            'type' => 'nullable|string|max:250',
            'product_types.*' => 'nullable|string|max:250',
            'country' => 'nullable|string|max:250',
            'city' => 'nullable|string|max:250',
            'phone' => 'nullable|string|max:250',
            'phone_code' => 'nullable|string|max:250',
            'official_website' => 'nullable|string|max:250',
            'email' => 'nullable|email|max:250',
        ]);
        $store->name = $request->name;
        if ($request->has('email')) {
            $store->email = $request->email;
        }
        if ($request->has('about')) {
            $store->about = $request->about;
        }
        if ($request->has('type')) {
            $store->type = $request->type;
        }
        if ($request->has('name')) {
            $store->name = $request->name;
        }
        if ($request->has('product_types')) {
            $store->product_types = $request->product_types;
        }
        if ($request->has('country')) {
            $store->country = $request->country;
        }
        if ($request->has('phone_code')) {
            $store->phone_code = $request->phone_code;
        }
        if ($request->has('city')) {
            $store->city = $request->city;
        }
        if ($request->has('official_website')) {
            $store->official_website = $request->official_website;
        }
        if ($request->has('phone')) {
            $store->phone = $request->phone;
        }

        if ($store->save()) {
            return response()->json([
                'message' => 'Brand has been Updated successfully',
                'store' => $store,
                'owner' => [
                    'name' => 'Muhamed Magdy',
                    'uid' => "GgZSlJOVS5hQsXH3ml9wrGOc5Zy1"
                ]
            ], 200);
        }
    }
}
