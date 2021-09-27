<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Auth;
use App\Mail\sendMail;
use App\Models\Collection;
use App\Models\CollectionProduct;
use App\Models\Store;
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
            Mail::to($user->email)->send(new sendMail($code));
            return response()->json(['message' => 'verification code has been sent to your email', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'something went wrong, try again later'], 500);
        }
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
            $user =  $this->auth->updateUser($uid, ['emailVerified' => true]);
            return response()->json([
                'message' => 'Email has been verified successfully',
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'message' => 'This code is wrong or expired'
            ], 405);
        }
    }

    public function updatePhoneNumber(Request $request)
    {
        $uid = $request->uid;
        $phone = $request->phone;

        if ($phone) {
            $user =  $this->auth->updateUser($uid, ['phoneNumber' => $phone, 'email' => $phone . "@arch17.com"]);
        }

        if ($user) {
            return response()->json([
                'message' => 'Phone has been Updated successfully',
                'user' => $user
            ], 200);
        }
        return response()->json([
            'message' => 'Something went Wrong, try again',
        ], 500);
    }

    public function registerUser(Request $request)
    {
        $this->validate($request, [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:250',
            'password' => 'required|string|max:255',
        ]);
        $email = $request->email;
        $password = $request->password;
        $fname = $request->fname;
        $lname = $request->lname;
        $user = $this->auth->createUserWithEmailAndPassword($email, $password);
        if ($user) {
            $newUser = $this->auth->updateUser($user->uid, ['displayName' => $fname . " " . $lname]);
            if ($newUser) {
                return response()->json([
                    'message' => "Registered Succeffully",
                    'user' => [
                        'uid' => $newUser->uid,
                        'displayName' => $newUser->displayName,
                        'email' => $newUser->email,
                        'emailVerified' => $newUser->emailVerified,
                        'photoURL' => $newUser->photoUrl ?? null,
                        'phoneNumber' => $newUser->phoneNumber,
                        'disabled' => $newUser->disabled,
                        'providerData' => $newUser->providerData ?? null

                    ]
                ], 200);
            }
        }

        return response()->json([
            'message' => "Error, try again",
        ], 500);
    }

    // login by firebase
    public function loginUser(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:250',
            'password' => 'required|string|max:255',
        ]);

        $signInResult = $this->auth->signInWithEmailAndPassword($request->email, $request->password);
        if ($signInResult) {
            $user = $signInResult->data();
            return response()->json([
                'message' => "Logged in Succeffully",
                'user' => [
                    'uid' => $user['localId'],
                    'displayName' => $user['displayName'],
                    'email' => $user['email'],
                    'emailVerified' => false,
                    'photoURL' => $user['profilePicture'] ?? null,
                    'phoneNumber' => null,
                    'disabled' => false,
                ]
            ], 200);
        }
        return response()->json([
            'message' => "Login Failed",
        ], 500);
    }

    public function updateDisplayName(Request $request)
    {
        $this->validate($request, [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
        ]);
        $uid = $request->uid;
        $fname = $request->fname;
        $lname = $request->lname;
        $user = $this->auth->updateUser($uid, ['displayName' => $fname . " " . $lname]);
        if ($user) {
            return response()->json([
                'message' => "Name has been Updated successfully",
                'user' => $user
            ], 200);
        }
        return response()->json([
            'message' => "Error occured, try again",
        ], 500);
    }

    public function updateProfilePic(Request $request)
    {
        $this->validate($request, [
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|between:1,5000'
        ]);

        $user = null;
        if ($request->hasFile('photo')) {
            $uid = $request->uid;
            $photoURL =   $request->photo->storeOnCloudinary()->getSecurePath();
            $user = $this->auth->updateUser($uid, ['photoURL' => $photoURL]);
        }
        if ($user) {
            return response()->json([
                'message' => "Photo has been Updated successfully",
                'user' => $user
            ], 200);
        }
        return response()->json([
            'message' => "Error occured, try again",
        ], 500);
    }


    // brand apis

    public function createBrand(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:250',
            'product_types' => 'required|array',
            'type' => 'required|string|max:250',
            'product_types.*' => 'required|string|max:250',
            'country' => 'required|string|max:250',
            'phone' => 'required|string|max:250',
            'email' => 'required|email|max:250',
        ]);
        if ($request->has('uid')) {
            $store = new Store();
            $store->user_id = $request->uid;
            $store->name = $request->name;
            $store->email = $request->email;
            $store->type = $request->type;
            $store->product_types = $request->product_types;
            $store->country = $request->country;
            $store->phone = $request->phone;
        }
        if ($store->save()) {
            return response()->json([
                'message' => 'Store has been created successfully',
                'store' => $store
            ], 201);
        }
        return response()->json([
            'message' => 'something went wrong, try again',
        ], 500);
    }

    public function getBrandById($id)
    {
        $store = Store::find($id);
        if (!$store) {
            return response()->json([
                'message' => 'Brand Not Found!',
            ], 404);
        }
        return response()->json([
            'store' => $store,
            'owner' => [
                'name' => 'Muhamed Magdy',
                'uid' => "GgZSlJOVS5hQsXH3ml9wrGOc5Zy1"
            ]
        ], 200);
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
            'phone' => 'nullable|string|max:250',
            'official_website' => 'nullable|string|max:250',
            'email' => 'nullable|email|max:250',
        ]);
        $store->name = $request->name;
        $store->email = $request->email;
        $store->type = $request->type;
        $store->about = $request->about;
        $store->product_types = $request->product_types;
        $store->country = $request->country;
        $store->city = $request->city;
        $store->phone = $request->phone;
        $store->official_website = $request->official_website;

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
    public function addProductToCollection(Request $request)
    {
        $this->validate($request, [
            'collection_name' => 'nullable|string|max:250',
            'collection_id' => 'nullable|string|max:250',
            'store_id' => 'nullable|string|max:250',
            'store_uid' => 'nullable|string|max:250',
        ]);

        if ($request->has('collection_id')) {
            $collection = Collection::find($request->collection_id);
            if (!$collection) {
                return response()->json([
                    'message' => 'Collection Not Found or deleted!',
                ], 404);
            } else {
                $this->createCollection($request->store_id, $request->product_id);
            }
        }
        $collection = new Collection();
        $collection->collection_name = $request->collection_name;
        $collection->store_id = $request->store_id;
        // $collection->store_uid = $request->store_uid;
        if ($collection->save()) {
            $this->createCollection($request->store_id, $request->product_id);
        }
    }
    public function createCollection($store_id, $product_id)
    {
        $collect_product = new CollectionProduct();
        $collect_product->store_id = $store_id;
        $collect_product->product_id = $product_id;

        if ($collect_product->save()) {
            return response()->json([
                'message' => 'Brand has been Updated successfully',
                'collection' => $collect_product,
            ], 201);
        } else {
            return response()->json([
                'message' => 'something went wrong, try again',
            ], 500);
        }
    }
}
