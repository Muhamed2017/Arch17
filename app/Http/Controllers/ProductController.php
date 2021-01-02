<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Support\Services\AddImagesToEntity;

use App\Models\Product;
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
    public function addOptionToProduct(Request $request, Product $product)
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


        $product_options->product_id = 1;
        $product_options->material_name = $request->material_name;
        $product_options->size = $request->size;
        $product_options->price = $request->price;
        $product_options->offer_price =  $request->offer_price;
        $product_options->quantity =  $request->quantity;

        if ($product_options->save()) {
            $this->attachRelatedModelsOptions($product_options, $request);

            return response()->json([
                'message' => 'option attached to product successfully',
                'options' => $product_options
            ], 200);
        }
    }


    // product description -step three
    public function addDescriptionToProduct(Request $request, Product $product)
    {
        $this->validate($request, [

            'description_text'    => 'required|array',
            'description_text.*'  => 'required|string',
            'description_media'   => 'nullable|array',
            'description_media.*' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',

        ]);

        $product_description = new ProductOptions();

        $product_description->product_id = $product->id;
        $product_description->material_name = $request->material_name;
        $product_description->product_id = $request->size;

        if ($product_description->save()) {

            $this->attachRelatedModelsDescription($product_description, $request);
            return response()->json(
                [
                    'message' => 'description attached to product successfully',
                    'options' => $product_description
                ],
                200
            );
        }
    }




    public function getValidationRules($id = '')
    {
        return [

            // Listing Scene
            'name' => 'required|string|max:250',
            'kind' => 'required|string|max:2000',
            'category' => 'required|string|max:2000',
            'style' => 'required|string|max:2000',

            // Listing Location
            'country' => 'required|string|max:250',
            'city' => 'required|string|max:250',
            // 'style' => 'required|string|max:250',
            'size' => 'required|array',
            'size.*.l' => 'required|numeric',
            'size.*.w' => 'required|numeric',
            'size.*.h' => 'required|numeric',

            'text_description' => 'nullable|array',
            'text_description.*' => 'nullable|string|max:250',

            'material_name' => 'required|array',
            'material_name.*' => 'string',

            // Listing Rules
            'places_tags' => 'nullable|array',
            'places_tags.*' => 'string',

            // Calender
            'price' => 'required|array',
            'price.*' => 'string',

            'offer_price' => 'required|array',
            'offer_price.*' => 'string',

            'quantity' => 'required|array',
            'quantity.*' => 'numeric'
        ];
    }

    public function getProductInput(Request $request)
    {
        $input = $request->only(
            'name',
            'type',
            'country',
            'city',
            'kind',
            'style',
            'places_tags',
            'text_description',
            'category'
        );

        return $input;
    }



    public function getProductOptionsInput(Request $request)
    {

        $input = $request->only(
            'size',
            'material_name',
            'price',
            'offer_price',
            'quantity',
        );

        return $input;
    }



    // public function attachRelatedModels($product, $request)
    // {
    //     if ($request->hasFile('cover')) (new AddImagesToEntity($request->cover, $product, ["width" => 1024]))->execute();
    // }

    public function attachRelatedModelsOptions($product_options, $request)
    {
        if ($request->hasFile('cover')) (new AddImagesToEntity($request->cover, $product_options, ["width" => 1024]))->execute();
        if ($request->hasFile('material_pic')) (new AddImagesToEntity($request->cover, $product_options, ["width" => 1024]))->execute();
    }

    public function attachRelatedModelsDescription($product_description, $request)
    {
        if ($request->hasFile('desctription_media')) (new AddImagesToEntity($request->cover, $product_description, ["width" => 1024]))->execute();
    }
}
