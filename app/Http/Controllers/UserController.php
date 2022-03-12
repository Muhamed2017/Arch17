<?php

namespace App\Http\Controllers;

use App\Models\BusinessAccount;
use App\Models\Product;
use App\Models\Collection;
use App\Models\Folder;
use App\Models\Follower;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Store;
use Illuminate\Validation\ValidationException;
use CloudinaryLabs\CloudinaryLaravel\Commands;

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
        $this->validate($request, [
            'proffession_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:business_account',
            'phone' => 'required|string|max:250',
            'passcode' => 'required|confirmed|min:6|max:255',

        ]);


        // creating the business account

        $bussiness = new BusinessAccount();
        $bussiness->user_id = $user_id;
        $bussiness->proffession_name = $request->proffession_name;
        $bussiness->phone = $request->phone;
        $bussiness->email = $request->email;
        $bussiness->passcode = bcrypt($request->get('passcode'));

        if ($bussiness->save()) {
            return response()->json([
                'message' => 'Your besiness account has been successfully created!',
                'Account' => $bussiness
            ], 201);
        } else {
            return response()->json([
                'status' => '02',
                'message' => 'Error occurs, try agian !'
            ], 500);
        }
    } //end of create business account api



    //create store api
    public function CreateStore(Request $request)
    {

        $user_id = auth('user')->user()->id;
        // $user_id = 4;
        $user = User::findOrFail($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User Not Found!',
            ], 404);
        }

        if (!$user->businessAccount) {
            return response()->json([
                'message' => 'You must create business account first!',
            ], 401);
        } else {
            $this->validate($request, [
                'name' => 'required|string|max:250',
                'country' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024'
            ]);

            $store = new Store();
            $store->name = $request->name;
            $store->business_account_id = $user->businessAccount->id;
            $store->user_id = $user_id;
            $store->country = $request->country;
        }

        if ($store->save()) {
            $media = Store::find($store->id);
            $media->attachMedia($request->photo);

            return response()->json([
                'message' => 'Store Created Successfully',
                'store' => $store,
                'user_stores' => $user->stores
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error occurs, try again'
            ], 500);
        }
    }
    //end of create store api
    public function create_product_collection(Request $request)
    {
        $product_id = $request->product_id;
        $collection_name = $request->collection_name;
        $product = Product::find($product_id);
        $product->collections()->create(['name' => $collection_name, 'user_id' => auth()->user()->id]);
        return response()->json([
            'message' => 'Collection Created Successfully',
            'product' =>  Product::find($product_id)->collections,
            'user_collections' => auth()->user()->collections
        ], 200);
    }
    public function add_product_to_collection(Request $request)
    {
        $product_id = $request->product_id;
        $collection_id = $request->collection_id;
        $product = Product::find($product_id);
        $collection = Collection::find($collection_id);
        $product->collections()->attach($collection);
        return response()->json([
            'message' => 'Collection',
            'product' =>  $product->collections,
            'collections' => $collection
        ], 200);
    }
    public function remove_product_from_collection(Request $request)
    {
        $product_id = $request->product_id;
        $collection_id = $request->collection_id;
        $product = Product::find($product_id);
        $product->collections()->detach($collection_id);
        return response()->json([
            'message' => 'Collection',
            'product' =>  $product->collections
        ], 200);
    }
    public function geUserProductCollections()
    {
        $user = auth()->user();
        foreach ($user->collections as $collection) {
            $collections = array();
            if ($collection->collection_type === 'product') {
                array_push($collections, $collection);
            }
        }
        return response()->json([
            'message' => 'Collection',
            'collections' =>  $collections
        ], 200);
    }


    // get all user collections (Folders) in user page
    public function getUserFolders($user_uid)
    {
        $collections = Folder::all()->where('user_id', $user_uid);
        $followrs = Follower::find($user_uid);
        $stores = $followrs->store();

        return response()->json([
            'status' => true,
            'collections' =>  $collections,
            'stores' => $stores
        ], 200);
    }
}
