<?php

namespace App\Http\Controllers;

use App\Models\BusinessAccount;
use App\Models\Product;
use App\Models\Collection;
use App\Models\Folder;
use App\Models\Follower;
use App\Models\FollowerStore;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Store;
use Illuminate\Validation\ValidationException;
use CloudinaryLabs\CloudinaryLaravel\Commands;
use Throwable;
use Kreait\Firebase\Auth;


class UserController extends Controller
{

    protected $auth;
    public function __construct(Auth  $auth)
    {
        $this->auth = $auth;
    }

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

    public function uploadAvatar(Request $request, $user_id)
    {
        $this->validate($request, [
            'img' => "nullable|mimes:jpeg,jpg,png|between:1,5000",
        ]);
        $user = User::find($user_id);
        $src = $request->img->storeOnCloudinary()->getSecurePath();
        $user->avatar = $src;

        $fb =$this->auth->updateUser($user_id, ['photoURL' => $src]);


        if ($user->save()) {
            return response()->json([
                'message' => "Successfully Imaged Uploaded!",
                'user' => $user,
                'fb'=>$fb
            ], 200);
        } else {
            return response()->json([
                'message' => "Error",
                'avatar' => null
            ], 200);
        }
    }
    public function updateUser(Request $request, $user_id)
    {
        $this->validate($request, [
            'displayName' => "nullable|string",
        ]);
        $user = User::find($user_id);

        if ($request->has('displayName')) {
            $fb = $this->auth->updateUser($user_id, ['displayName' => $request->displayName]);
            $user->displayName = $request->displayName;
        }
        if ($request->has('email')) {
            $fb = $this->auth->updateUser($user_id, ['email' => $request->email]);
            $user->email = $request->email;
        }
        if ($request->has('phoneNumber')) {
            $fb = $this->auth->updateUser($user_id, ['phoneNumber' => $request->phoneNumber]);
            $user->phoneNumber = $request->phoneNumber;
        }
        if ($request->has('country')) {
            $user->country = $request->country;
        }
        if ($request->has('city')) {
            $user->city = $request->city;
        }
        if ($request->has('professions')) {
            $user->professions = $request->professions;
        }

        if ($user->save()) {
            return response()->json([
                'message' => "Successfully profile Updated!",
                'user' => $user,
                'fb' => $fb
            ], 200);
        } else {
            return response()->json([
                'message' => "Error",
                'user' => null
            ], 200);
        }
    }

    public function deleteUser($uid)
    {
        $user = User::find($uid);

        if (!$user) {
            return response()->json([
                'message' => "User Not Found or deleted!"
            ], 404);
        } else {
            try {
                $user->delete();
                $this->auth->deleteUser($uid);
                return response()->json([
                    'success' => true,
                    'message' => "User deleted Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => $err
                ], 500);
            }
        }
    }
    // get all user collections (Folders) in user page
    public function getUserFolders($user_uid)
    {
        $collections = Folder::all()->where('user_id', $user_uid);
        $follower = Follower::all()->where('follower_id', $user_uid)->first();
        $user = User::find($user_uid);
        $followed_store = [];
        if ($follower) {
            $followed_store = $follower->stores()->get();
        }
        return response()->json([
            'status' => true,
            'collections' =>  $collections,
            'user' => $user,
            'followed_stores' => $followed_store
        ], 200);
    }

    public function getUserCollectionById($id)
    {

        $collection = Folder::find($id);
        $products = $collection->products()->get();
        return response()->json([
            'status' => true,
            'collection' =>  $collection,
            'products' => $products
        ], 200);
    }

    public function editCollection(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:250',
        ]);

        $collection = Folder::find($id);
        $collection->name = $request->name;
        if ($collection->save()) {
            return response()->json([
                'status' => true,
                'collection' =>  $collection,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
            ], 500);
        }
    }
    public function deleteCollection($id)
    {

        $collection = Folder::find($id);

        if ($collection) {
            try {
                $collection->delete();
                return response()->json([
                    'success' => true,
                    'message' => "Collection deleted Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => $err
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Collection Not Found"
            ], 200);
        }
    }


    // passport test
    public function getAllUsers()
    {
        $users = User::all();

        return response()->json([
            'status' => true,
            'users' =>  $users,
        ], 200);
    }


    // register api with firebase

    public function registerUser(Request $request)
    {
        $this->validate($request, [
            'displayName' => 'required|string|max:250',
            'providerId' => 'required|string|max:250',
            'uid' => 'required|string|max:250',
            'email' => 'nullable|string|max:250',
            'avatar' => 'nullable|string|max:250',
        ]);

        $user = new User();
        $user->displayName = $request->displayName;
        $user->providerId = $request->providerId;
        $user->uid = $request->uid;
        $user->email = $request->email;
        $user->avatar = $request->avatar;

        if ($user->save()) {
            return response()->json([
                'success' => true,
                'user' =>  $user
            ], 200);
        } else {
            return response()->json([
                'success' => false,
            ], 200);
        }
    }

    public function upgradeUserToDesigner(Request $request, $user_uid)
    {
        $this->validate($request, [
            'country' => 'required|string|max:250',
            'city' => 'required|string|max:250',
            'phoneCode' => 'required|string|max:250',
            'phoneNumber' => 'nullable|string|max:250',
            'professions' => 'required|array',
            'professions.*' => 'string|max:250'
        ]);

        $user = User::find($user_uid);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not found"
            ], 200);
        }
        $user->country = $request->country;
        $user->city = $request->city;
        $user->phoneCode = $request->phoneCode;
        $user->professions = $request->professions;
        $user->phoneNumber = $request->phoneNumber;
        $user->country = $request->country;
        $user->is_designer = 1;
        if ($user->save()) {
            return response()->json([
                'success' => true,
                'user' =>  $user
            ], 200);
        } else {
            return response()->json([
                'success' => false,
            ], 200);
        }
    }
}
