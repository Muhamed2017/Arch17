<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Support\Services\AddImagesToEntity;

use App\Models\Product;
use App\Models\ProductOptions;

class ProductController extends Controller
{



    public function AddProduct(Request $request){


        $this->validate($request, $this->getValidationRules());
        $this->validate($request, [
            'cover' => 'nullable|array',
            // 'cover.*.option.*' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',
            // 'material_img' => 'nullable|array',
            'cover.*' => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024'
        ]);

        $user_id = auth('user')->user()->id;
        $user = User::findOrFail($user_id);
        $product_input = $this->getProductInput($request);
        $options_input = $this->getProductOptionsInput($request);

        $product_input['store_id'] = 1;
        $product_input['business_account_id'] = 1;
        $product_input['user_id'] = $user_id;
        $product = new Product($product_input);


        if($product->save()){
            $options_input['product_id'] =  $product->id;
            $options  = new ProductOptions($options_input);

            // $this->attachRelatedModels($product, $request);

            if($options->save()){
                $this->attachRelatedModelsOptions($options, $request);
                return response()->json([
                 'message'=>'Product added sucssefully',
                 'product'=>$product,
                 'options'=>$product->options

                ], 200);
            }
        }else{

            return  response()->json([
                'message'=>'error occurd'
            ], 500);
        }

    }




        public function getValidationRules($id = ''){
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
            'name', 'type', 'country', 'city', 'kind', 'style',
            'places_tags', 'text_description', 'category'
        );

        return $input;
    }

    public function getProductOptionsInput(Request $request){

        $input = $request->only(
            'size','material_name',
            'price', 'offer_price', 'quantity',
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
}
