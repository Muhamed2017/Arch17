<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Support\Services\AddImagesToEntity;

use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductIdentity;
use App\Models\ProductOptions;
use CloudinaryLabs\CloudinaryLaravel\Model\Media;
use PhpOption\Option;

use function GuzzleHttp\Promise\each;

class ProductController extends Controller
{


    // product entity - step zero

    public function AddProduct(Request $request)
    {
        $this->validate($request, [
            'kind'          => 'required|string|max:2000',
        ]);

        // $product->store_id = $store_id;
        // $product->user_id = $user->id;
        // $product->business_account_id = $business_account_id;
        $product = new Product();
        $product->store_id = 1;
        $product->user_id = 1;
        $product->business_account_id = 1;
        $product->kind = $request->kind;

        if ($product->save()) {
            $product->identity()->create();
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

    // product identity - step one
    public function AddProductIdentity(Request $request, $id)
    {
        // $user = auth('user')->user();

        $this->validate($request, [
            // step one
            'name'          => 'required|string|max:250',
            'kind'          => 'required|string|max:2000',
            'type'          => 'nullable|string|max:2000',
            'category'      => 'required|string|max:2000',
            'style'         => 'required|string|max:2000',
            'kind'          => 'required|string|max:250',
            'material'      => 'nullable|string|max:250',
            'base'      =>     'nullable|string|max:250',
            'seats'      =>     'nullable|string|max:250',
            'shape'      =>     'nullable|string|max:250',
            'country'       => 'required|string|max:250',
            'places_tags'   => 'required|array',
            'places_tags.*' => 'string|max:250',
            'is_outdoor'    => 'nullable|string|max:250',
            'is_for_kids'    => 'nullable|string|max:250',
            'product_file_kind' => 'nullable|string|max:250',
        ]);
        $product = Product::findOrFail($id);
        $product_identity = ProductIdentity::findOrFail($product->id);
        $product_identity->name = $request->name;
        $product_identity->kind = $request->kind;
        $product_identity->places_tags = $request->places_tags;
        $product_identity->style = $request->style;
        $product_identity->category = $request->category;
        $product_identity->country = $request->country;
        $product_identity->type = $request->type;
        $product_identity->base = $request->base;
        $product_identity->shape = $request->shape;
        $product_identity->seats = $request->seats;
        $product_identity->material = $request->material;
        $product_identity->is_outdoor = $request->is_outdoor;
        $product_identity->is_for_kids = $request->is_for_kids;
        $product_identity->product_file_kind = $request->product_file_kind;
        if ($product_identity->save()) {
            $options_prices = new ProductOptions();
            $options_prices->product_id = $product_identity->product_id;
            if ($options_prices->save()) {
                return response()->json([
                    'message' => 'product_identity created, ready to add option and price',
                    'identity' => $product_identity,

                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Error occured, try agian later'
            ], 500);
        }
    }


    // product options and price - step two
    public function addOptionToProduct(Request $request, $id)
    {

        $data = $request->all();
        $row_covers = $request->allFiles();
        foreach ($data as $option) {
            $option_price = new ProductOptions($option);
            $option_price->product_id = $id;
            // $option_price->save();
            if ($option_price->save()) {
                if (!empty($row_covers)) {
                    foreach ($row_covers as $covers) {
                        foreach ($covers as $cover) {
                            $option_price->attachMedia($cover);
                        }
                    }
                }
            }
            // $option_price->save();
        }

        return response()->json($data);
        // return $covers;
    }
    // $this->validate($request, [
    //     'material_name' => 'required|string|max:250',
    //     'size'          => 'required|string|max:2000',
    //     'price'         => 'required|string|max:2000',
    //     'offer_price'   => 'required|string|max:2000',
    //     'quantity'      => 'required|string|max:250',
    //     'cover'         => 'nullable|array',
    //     'cover.*'       => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',
    //     'material_pic'  => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024'
    // ]);

    // $product = Product::findOrFail($id);
    // // $product_options = $product->options();
    // $product_options = new ProductOptions();

    // $product_options->product_id = $product->id;
    // $product_options->material_name = $request->material_name;
    // $product_options->size = $request->size;
    // $product_options->price = $request->price;
    // $product_options->offer_price =  $request->offer_price;
    // $product_options->quantity =  $request->quantity;

    // if ($product_options->save()) {
    //     // $product_options->attachMedia($request->cover);
    //     // $product_options->attachMedia($request->material_pic);
    //     return response()->json([
    //         'message' => 'option attached to product successfully',
    //         'options' => $product->options
    //     ], 200);
    // }

    // return $request->all();
    // }


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

    public function testImageUpload(Request $request)
    {
        $this->validate($request, [
            'img'   => 'nullable|array',
            'img.*' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000'

        ]);
        $product = Product::find(1);

        if ($request->hasFile('img')) {

            foreach ($request->img as $img) {
                $product->attachMedia($img);
            }
        }


        return response()->json([
            'message' => "Successfully Imaged Uploaded!",
            'img' => $product->fetchAllMedia()
        ], 200);
    }

    // }
}
