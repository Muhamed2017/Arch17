<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Support\Services\AddImagesToEntity;

use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductIdentity;
use App\Models\ProductOptions;
use App\Models\ProductFiles;
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
        $product = Product::find($id);
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
        $this->validate($request, [
            'material_name' => 'required|string|max:250',
            'material_image' => 'required|mimes:png,jpg|between:1,10000',
            'size'          => 'nullable|string|max:2000',
            'price'         => 'nullable|string|max:2000',
            'offer_price'   => 'nullable|string|max:2000',
            'quantity'      => 'required|string|max:250',
            'cover'         => 'required|array',
            'cover.*'       => 'required|mimes:jpeg,jpg,png|between:1,10000',
            // 'cover.*'       => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',
            // 'cover.*'       => 'nullable|image|mimes:jpeg,bmp,jpg,png|between:1,6000|dimensions:min_width=1024,max_height=1024',

        ]);
        $product = Product::find($id);
        $product_options = new ProductOptions();
        $product_options->product_id = $product->id;
        $product_options->material_name = $request->material_name;
        $product_options->size = $request->size;
        $product_options->price = $request->price;
        $product_options->offer_price =  $request->offer_price;
        $product_options->quantity =  $request->quantity;
        $cover_path = [];
        foreach ($request->cover as $cover) {
            array_push($cover_path, $cover->storeOnCloudinary()->getSecurePath());
        }
        $product_options->material_image =  $request->material_image->storeOnCloudinary()->getSecurePath();
        $product_options->cover = $cover_path;

        if ($product_options->save()) {
            return response()->json([
                'message' => 'option attached to product successfully',
                'options' => $product_options
            ], 200);
        }
        return response()->json([
            'message' => 'Something went wrong ',
        ], 500);
    }




    public function ProductDescription(Request $request, $id)
    {
        $this->validate($request, [
            'desc_overview_img'   => 'nullable|array',
            'desc_overview_img.*' => 'nullable|mimes:jpeg,bmp,jpg,png|between:1,10000',
            'desc_mat_desc_img'   => 'nullable|array',
            'desc_mat_desc_img.*' => 'nullable|mimes:jpeg,bmp,jpg,png|between:1,10000',
            'desc_dimension_img'   => 'nullable|array',
            'desc_dimension_img.*' => 'nullable|mimes:jpeg,bmp,jpg,png|between:1,10000',
            'desc_gallery_files'   => 'nullable|array',
            'desc_gallery_files.*' => 'nullable|mimes:jpeg,bmp,jpg,png,mp4|between:1,10000'
        ]);
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        $product_desc = null;
        if ($product->description) {
            // $product_desc = ProductDescription::with("")->where("product_id", $product->id);
            $product_desc = ProductDescription::find($product->description->id);
        } else {
            $product_desc = new ProductDescription();
            $product_desc->product_id = $product->id;
        }

        // $product_desc = new ProductDescription();
        // $product_desc->product_id = $product->id;
        $overview_path = [];
        $materials_path = [];
        $dimensions_path = [];
        $gallery_path = [];

        if ($request->hasFile('desc_overview_img')) {
            foreach ($request->desc_overview_img as $img) {
                array_push($overview_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_desc->desc_overview_img = $overview_path;
        }
        if ($request->hasFile('desc_mat_desc_img')) {
            foreach ($request->desc_mat_desc_img as $img) {
                array_push($materials_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_desc->desc_mat_desc_img = $materials_path;
        }
        if ($request->hasFile('desc_dimension_img')) {
            foreach ($request->desc_dimension_img as $img) {
                array_push($dimensions_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_desc->desc_dimension_img = $dimensions_path;
        }
        if ($request->hasFile('desc_gallery_files')) {
            foreach ($request->desc_gallery_files as $img) {
                array_push($gallery_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_desc->desc_gallery_files = $gallery_path;
        }
        if ($product_desc->save()) {
            return response()->json([
                'message' => 'product description added successfully',
                'product_desc' => $product_desc,
            ], 201);
        }
    }

    public function ProductDescriptionContent(Request $request, $id)
    {

        $this->validate($request, [
            'overview_content'   => 'nullable|string',
            'mat_desc_content'   => 'nullable|string',
            'size_content'   => 'nullable|string',
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        $product_desc = null;
        if ($product->description) {
            // $product_desc = ProductDescription::with("")->where('product_id', $product->id);
            // $product_desc = $product->description();
            $product_desc = ProductDescription::find($product->description->id);
        } else {
            $product_desc = new ProductDescription();
            $product_desc->product_id = $product->id;
        }

        if ($request->has('overview_content')) {

            $product_desc->overview_content = $request->overview_content;
        }

        if ($request->has('mat_desc_content')) {

            $product_desc->mat_desc_content = $request->mat_desc_content;
        }
        if ($request->has('size_content')) {

            $product_desc->size_content = $request->size_content;
        }

        if ($product_desc->save()) {
            return response()->json([
                'message' => 'product description added successfully',
                'product_desc' => $product_desc,
            ], 201);
        }
    }


    public function ProductFiles(Request $request, $id)
    {
        $this->validate($request, [
            'files_cad_2d'   => 'nullable|array',
            'files_cad_2d.*' => 'nullable|mimes:dwg',
            'files_3d'   => 'nullable|array',
            'files_3d.*' => 'nullable|mimes:3ds,skp,obj',
            'files_pdf_cats'   => 'nullable|array',
            'files_pdf_cats.*' => 'nullable|mimes:pdf',
        ]);
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        $product_files = new ProductFiles();
        $product_files->product_id = $product->id;

        $two_d_files_path = [];
        $three_d_files_path = [];
        $pfd_cats_files_path = [];

        if ($request->hasFile('files_cad_2d')) {
            foreach ($request->files_cad_2d as $img) {
                array_push($two_d_files_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_files->files_cad_2d = $two_d_files_path;
        }
        if ($request->hasFile('files_3d')) {
            foreach ($request->files_3d as $img) {
                array_push($three_d_files_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_files->files_3d = $three_d_files_path;
        }
        if ($request->hasFile('files_pdf_cats')) {
            foreach ($request->files_pdf_cats as $img) {
                array_push($pfd_cats_files_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_files->files_pdf_cats = $pfd_cats_files_path;
        }

        if ($product_files->save()) {
            return response()->json([
                'message' => 'product description added successfully',
                'product_desc' => $product_files,
            ], 201);
        }
    }


    public function getProductById($id)
    {

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }

        return response()->json([
            'product' => $product
        ], 200);
    }
    public function testImageUpload(Request $request)
    {
        $this->validate($request, [
            'img'   => 'nullable|array',
            'img.*' => 'nullable|mimes:jpeg,bmp,jpg,png,mp4'

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
}
