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
use App\Models\Product;
use App\Models\ProductIdentity;
use App\Models\Store;
use App\Models\Subscriber;
use App\Models\Type;
use App\Models\UserVerifications;
use Throwable;

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




    public function createCollection($collection_id, $product_id)
    {
        $collect_product = new CollectionProduct();
        $collect_product->collection_id = $collection_id;
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

    public function addProductToExistingCollection(Request $request)
    {
        $this->validate($request, [
            'collection_id' => 'nullable|string|max:250',
            'product_id' => 'nullable|string|max:250',
        ]);


        $collecting = new CollectionProduct();
        $collecting->product_id = $request->product_id;
        $collecting->collection_id = $request->collection_id;
        if ($collecting->save()) {
            return response()->json([
                'message' => 'Collection has been created and product has been added to it.',
                'product_collected' => $collecting
            ], 201);
        } else {
            return response()->json([
                'message' => 'Error occured, try again later',
            ], 500);
        }
    }
    public function addProductToNewColelction(Request $request)
    {
        $this->validate($request, [
            'collection_name' => 'required|string|max:250',
            'store_id' => 'nullable|string|max:250',
            'product_id' => 'nullable|string|max:250',
        ]);
        $collection = new Collection();
        $collection->collection_name = $request->collection_name;
        $collection->store_id = $request->store_id;

        if ($collection->save()) {
            return $this->createCollection($collection->id, $request->product_id);
        } else {
            return response()->json([
                'message' => 'Error occured, try again later',
            ], 500);
        }
    }
    public function getAllCollectionsbyStoreId($store_id)
    {
        $collections = Collection::with('products')->where('store_id', $store_id)->get();
        return response()->json([
            'collections' => $collections
        ], 200);
    }
    public function getStoreIdByProductId($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json([
                'message' => "Product Not Found or deleted!"
            ], 404);
        } else {
            $store_id = $product->store_id;
            return response()->json([
                'store_id' => $store_id
            ], 200);
        }
    }
    public function editBrandById(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'nullable|string|max:250',
            'product_types' => 'nullable|array',
            'type' => 'nullable|string|max:250',
            'product_types.*' => 'nullable|string|max:250',
            'country' => 'nullable|string|max:250',
            'city' => 'nullable|string|max:250',
            'phone' => 'nullable|string|max:250',
            'email' => 'nullable|email|max:250',
            'logo' => "nullable|mimes:jpeg,jpg,png|between:1,5000",
            'cover' => "nullable|mimes:jpeg,jpg,png|between:1,5000",
            'official_website' => 'nullable|string|max:250',
        ]);

        $brand = Store::find($id);

        if (!$brand) {
            return response()->json([
                'message' => 'Brand Not Found or deleted'
            ], 404);
        }

        if ($request->hasFile('cover')) {
            $brand->cover = $request->cover->storeOnCloudinary()->getSecurePath();
        }
        if ($request->has('product_types')) {
            $brand->product_types = $request->product_types;
        }


        $brand->name = $request->name;
        $brand->email = $request->email;
        $brand->phone = $request->phone;
        $brand->country = $request->country;
        $brand->city = $request->city;
        $brand->about = $request->about;
        $brand->type = $request->type;
        $brand->official_website = $request->official_website;
        if ($request->hasFile('logo')) {
            $brand->logo = $request->logo->storeOnCloudinary()->getSecurePath();
        }

        if ($brand->save()) {
            return response()->json([
                'brand' => $brand,
                'message' => 'Brand information has been updated'
            ], 200);
        } else {
            return response()->json([
                'message' => 'error occured, try again'
            ], 500);
        }
    }

    public function editNameForProductPublishing(Request $request, $identity_id)
    {

        $product_identity = ProductIdentity::find($identity_id);

        if (!$product_identity) {
            return response()->json([
                'message' => 'Not Found, try again'
            ], 404);
        }

        $product_identity->name = $request->display_name;
        if ($product_identity->save()) {
            return response()->json([
                'identity' => $product_identity,
                'message' => 'Product Name has been updated'
            ], 200);
        } else {
            return response()->json([
                'message' => 'error occured, try again'
            ], 500);
        }
    }


    public function previewProduct(Request $request)
    {
        $this->validate($request, [
            'preview_cover' => "nullable|mimes:jpeg,jpg,png|between:1,10000"
        ]);

        $identity_id = $request->identity_id;
        $product_identity = ProductIdentity::find($identity_id);
        if (!$product_identity) {
            return response()->json([
                'message' => 'Not Found, try again'
            ], 404);
        }

        $product_identity->name = $request->display_name;
        $product_identity->preview_price = $request->preview_price;

        if ($request->hasFile('preview_cover')) {
            $product_identity->preview_cover = $request->preview_cover->storeOnCloudinary()->getSecurePath();
        }

        if ($product_identity->save()) {
            try {
                $type = Type::updateOrCreate(
                    [
                        'store_id' => $product_identity->product->store_id,
                        'name' => $product_identity->kind,
                    ],
                    [
                        'name' => $product_identity->kind,
                        'preview' => $product_identity->preview_cover
                    ]
                );
                return response()->json([
                    'identity' => $product_identity,
                    'type' => $type,
                    'message' => 'Product Name has been updated',
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'status' => false,
                    'error' => $err
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'error occured, try again'
            ], 500);
        }
    }






    // subscribe by email in homepage..
    public function subscribe(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255|unique:subscribers,email',
            'proccessing_personal_data_approval' => 'nullable|string'
        ]);

        try {
            $subscriber = new Subscriber();
            $subscriber->email = $request->email;
            $subscriber->proccessing_personal_data_approval = $request->proccessing_personal_data_approval;
            $subscriber->save();
            return response()->json([
                'success' => true,
                'message' => "Subscribed Successfully",
                'subscriber' => $subscriber
            ], 201);
        } catch (Throwable $err) {
            return response()->json([
                'message' => "Error Occered",
                'error' => $err
            ], 500);
        }
    }
}
