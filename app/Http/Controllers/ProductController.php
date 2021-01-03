<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Support\Services\AddImagesToEntity;

use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductOptions;
use PhpOption\Option;

class ProductController extends Controller
{


    // product identity - step one
    public function AddProduct(Request $request)
    {
        $user = auth('user')->user();
        $store_id = 1;
        $business_account_id = 1;

        $this->validate($request, [
            // step one
            'name'          => 'required|string|max:250',
            'kind'          => 'required|string|max:2000',
            'category'      => 'required|string|max:2000',
            'style'         => 'required|string|max:2000',
            'country'       => 'required|string|max:250',
            'country'       => 'required|string|max:250',
            'text_description'       => 'required|string|max:250',
            'places_tags'   => 'required|array',
            'city'          => 'required|string|max:250',
            'places_tags.*' => 'string|max:250',

        ]);

        if (!$user) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
        $product = new Product();
        $product->store_id = $store_id;
        $product->user_id = $user->id;
        $product->business_account_id = $business_account_id;
        $product->name = $request->name;
        $product->kind = $request->kind;
        $product->places_tags = $request->places_tags;
        $product->style = $request->style;
        $product->category = $request->category;
        $product->country = $request->country;
        $product->city = $request->city;
        $product->text_description = $request->text_description;


        if ($product->save()) {
            return response()->json([
                'message' => 'product_identity created, ready to add option and price',
                'product' => $product,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Error occured, try agian later'
            ], 500);
        }
    }


    // product options and price - step two
    public function addOptionToProduct(Request $request, $id)
    {
        $this->validate($request, [
            'material_name' => 'required|string|max:250',
            'size'          => 'required|string|max:2000',
            'price'         => 'required|string|max:2000',
            'offer_price'   => 'required|string|max:2000',
            'quantity'      => 'required|string|max:250',
            'cover'         => 'nullable|array',
            'cover.*'       => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',
            'material_pic'  => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024'

        ]);

        $product_options = new ProductOptions();
        $product = Product::findOrFail($id);
        $product_options->product_id = $product->id;
        $product_options->material_name = $request->material_name;
        $product_options->size = $request->size;
        $product_options->price = $request->price;
        $product_options->offer_price =  $request->offer_price;
        $product_options->quantity =  $request->quantity;

        if ($product_options->save()) {
            $this->attachRelatedModels($product_options, $request);

            return response()->json([
                'message' => 'option attached to product successfully',
                'options' => $product->options
            ], 200);
        }
    }


    // product description -step three
    public function addDescriptionToProduct(Request $request, $id)
    {
        $this->validate($request, [
            'description_text'    => 'required|array',
            'description_text.*'  => 'string',
            'description_media'   => 'nullable|array',
            'description_media.*' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',

        ]);

        $product_description = new ProductDescription();
        $product = Product::findOrFail($id);

        $product_description->product_id = $product->id;
        $product_description->description_text = $request->description_text;

        if ($product->description) {
            return response()->json([
                'message' => 'product has already a description',
                'product_description' => $product->description

            ], 409);
        }
        if ($product_description->save()) {

            $this->attachRelatedModels($product_description, $request);
            return response()->json(
                [
                    'message' => 'description attached to product successfully',
                    'product_description' => $product->description
                ],
                200
            );
        }
    }



    public function attachRelatedModels($entity, $request)
    {
        if ($request->hasFile('cover')) (new AddImagesToEntity($request->cover, $entity, ["width" => 1024]))->execute();
        if ($request->hasFile('material_pic')) (new AddImagesToEntity($request->material_pic, $entity, ["width" => 1024]))->execute();
        if ($request->hasFile('description_media')) (new AddImagesToEntity($request->description_media, $entity, ["width" => 1024]))->execute();
    }

    // public function attachdescr($product_description, $request)
    // {
    // }
}
