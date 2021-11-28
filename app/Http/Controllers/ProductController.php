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
use App\Models\ProductGallery;
use App\Models\Store;
use CloudinaryLabs\CloudinaryLaravel\Model\Media;
use PhpOption\Option;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;
use Illuminate\Support\Facades\DB;


use function GuzzleHttp\Promise\each;

class ProductController extends Controller
{


    // product entity - step zero

    public function AddProduct(Request $request, $store_id)
    {
        $this->validate($request, [
            'kind'          => 'required|string|max:2000',
        ]);

        $store = Store::find($store_id);
        if (!$store) {
            return response()->json([
                'message' => 'No Sotre with this record, create store then add product'
            ], 404);
        }
        $product = new Product();
        $product->store_id = $store_id;
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
            'category'      => 'required|string|max:2000',
            'type'          => 'nullable|array',
            'type.*'          => 'nullable|string|max:2000',
            'style'         => 'nullable|array',
            'style.*'         => 'nullable|string|max:2000',
            'kind'          => 'required|string|max:250',
            'material'      => 'nullable|array',
            'material.*'      => 'nullable|string|max:250',
            'base'      =>     'nullable|array',
            'base.*'      =>     'nullable|string|max:250',
            'seats'      =>     'nullable|array',
            'seats.*'      =>     'nullable|string|max:250',
            'shape'      =>     'nullable|array',
            'shape.*'      =>     'nullable|string|max:250',
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
                    'tab_index' => 1
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Error occured, try agian later'
            ], 500);
        }
    }


    // product options and price - step two
    public function addOptionToProduct(Request $request, $id, $option_id)
    {
        $this->validate($request, [
            'material_name' => 'nullable|string|max:250',
            'material_image' => 'nullable|mimes:png,jpg|between:1,10000',
            'size'          => 'nullable|string|max:2000',
            'price'         => 'nullable|string|max:2000',
            'offer_price'   => 'nullable|string|max:2000',
            'quantity'      => 'nullable|string|max:250',
            'code'          => 'nullable|string|max:250',
            'cover'         => 'nullable|array',
            'cover.*'       => 'nullable|mimes:jpeg,jpg,png|between:1,10000',

        ]);
        $product = Product::find($id);
        if (nullOrEmptyString($request->material_name)) {
            return false;
        } else {
            $product_options = ProductOptions::find($option_id);
            $product_options->product_id = $product->id;
            $product_options->material_name = $request->material_name;
            $product_options->size = $request->size;
            $product_options->price = $request->price;
            $product_options->offer_price =  $request->offer_price;
            $product_options->quantity =  $request->quantity;
            $product_options->code =  $request->code;
            $product_options->material_image =  $request->material_image->storeOnCloudinary()->getSecurePath();
            if ($product_options->save()) {
                return response()->json([
                    'message' => 'option attached to product successfully',
                    'options' => $product_options
                ], 200);
            }
        }
        return response()->json([
            'message' => 'Something went wrong ',
        ], 500);
    }

    public function UpdateOrCreateOption(Request $request)
    {
        try {
            $option = ProductOptions::updateOrCreate(
                [
                    'id' => $request->option_id,
                ],
                [
                    'product_id' => $request->product_id,
                    'material_name' => $request->material_name,
                    'material_image' => "skemkemekmek",
                    'price' => '100',
                    'offer_price' => '50',
                    'size' => '500L 300W 600H',
                    'cover' => ["smmeenekn", 'lmelmelme']
                ]
            );
            return response()->json([
                'message' => 'Updated Successfully',
                'option' => $option
            ], 200);
        } catch (Throwable $er) {
            return $er;
        }
    }



    public function ProductDescription(Request $request, $id)
    {
        $this->validate($request, [
            'desc_gallery_files'   => 'nullable|array',
            'desc_gallery_files.*' => 'nullable|mimes:jpg,jpeg,png'
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        $product_gallery = new ProductGallery();
        $product_gallery->product_id = $id;
        $gallery_path = [];
        if ($request->hasFile('desc_gallery_files')) {
            foreach ($request->desc_gallery_files as $img) {
                array_push($gallery_path, $img->storeOnCloudinary()->getSecurePath());
            }
            $product_gallery->desc_gallery_files = $gallery_path;
        }
        if ($product_gallery->save()) {
            return response()->json([
                'message' => 'product description added successfully',
                'product_desc' => $product,
            ], 201);
        } else {
            return response()->json([
                'message' => 'error',
            ], 500);
        }
    }

    public function ProductDescriptionContent(Request $request, $id)
    {

        $this->validate($request, [
            'mat_desc_content'   => 'nullable|string',
            'size_content'   => 'nullable|string',
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        if ($request->desc_id != "") {
            $product_desc = ProductDescription::find($request->desc_id);
        } else {
            $product_desc = new ProductDescription();
            $product_desc->product_id = $product->id;
        }
        if ($request->has('mat_desc_content')) {
            // $product->description()->update([
            //     'mat_desc_content' => $request->mat_desc_content
            // ]);
            $product_desc->mat_desc_content = $request->mat_desc_content;
        }
        if ($request->has('size_content')) {
            // $product->description()->update([
            //     'size_content' => $request->size_content
            // ]);
            $product_desc->size_content = $request->size_content;
        }

        if ($product_desc->save()) {
            return response()->json([
                'message' => 'product description added successfully',
                'product_desc' => $product,
            ], 201);
        } else {
            return response()->json([
                'message' => 'error happend',
                // 'product_desc' => $product,
            ], 500);
        }
    }


    public function ProductDescriptionCotent(Request $request, $id)
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
        $product_desc = new ProductDescription();
        $product_desc->product_id = $product->id;
        $product_desc->overview_content = $request->overview_content;
        $product_desc->mat_desc_content = $request->mat_desc_content;
        $product_desc->size_content = $request->size_content;

        if ($product_desc->save()) {
            return response()->json([
                'message' => 'product description Overview added successfully',
                'product_desc' => $product_desc,
            ], 201);
        }
        if ($product_desc->save()) {
            return response()->json([
                'message' => 'Error Happenned',
            ], 500);
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
                array_push($two_d_files_path, $img->storeOnCloudinaryAs("dwgs", "file.dwg")->getSecurePath());
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
    public function testImageUpload(Request $request, $id)
    {
        $this->validate($request, [
            'img'   => 'nullable|array',
            'img.*' => "required|mimes:jpeg,jpg,png|between:1,5000"

        ]);
        $product = Product::find($id);
        if ($request->hasFile('img')) {
            foreach ($request->img as $img) {
                $product->attachMedia($img);
            }
        }

        $latest_img = count($product->fetchAllMedia()) - 1;
        return response()->json([
            'message' => "Successfully Imaged Uploaded!",
            'img' => $product->fetchAllMedia(),
            'lastIndex' => $latest_img
        ], 200);
    }

    public function attachProductOptionPictures(Request $request, $id)
    {
        $this->validate($request, [
            'cover'   => 'nullable|array',
            'cover.*' => "required|mimes:jpeg,jpg,png|between:1,5000"
        ]);
        $product = Product::find($id);
        $product_options = new ProductOptions();
        $product_options->product_id = $product->id;
        $cover_path = [];
        foreach ($request->cover as $cover) {
            array_push($cover_path, $cover->storeOnCloudinary()->getSecurePath());
        }
        $product_options->cover = $cover_path;
        if ($product_options->save()) {
            return response()->json([
                'message' => 'option attached to product successfully',
                'options' => $product_options,
                'option_id' => $product_options->id
            ], 200);
        }
        return response()->json([
            'message' => 'Something went wrong',
        ], 500);
    }

    public function getAllProducts()
    {
        $products = Product::all();
        if (!empty($products)) {
            return response()->json([
                'products' => $products,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }

    public function filterProductSearchPage(Request $request)
    {
        // $products = Product::all();
        // QueryBuilderRequest::setArrayValueDelimiter('|');

        $products = QueryBuilder::for(ProductIdentity::class)
            ->allowedFilters([
                AllowedFilter::exact('category'),
                AllowedFilter::exact('is_outdoor'),
                AllowedFilter::exact('is_for_kids'),
                AllowedFilter::exact('product_file_kind'),
                AllowedFilter::exact('kind'),
                'type', 'seats', 'base', 'shape', 'style'
            ])
            ->allowedAppends(['product'])
            ->get();
        if (!empty($products)) {
            return response()->json([
                'products' => $products,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }


    public function fakeOptionsData()
    {

        $options = collect([
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://bit.ly/3rfV6TZ", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3CY3Q3i", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 650, "height" => 600], "option_id" => 5],
                    ["src" => "https://bit.ly/3xs7S2I", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],
                    ["src" => "https://bit.ly/3E5g2Aw", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 490, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3lgabky", "cover_id" => 45, "cropping_data" => ["x" => 0, "y" => 80, "width" => 290, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3rb34xA", "cover_id" => 55, "cropping_data" => ["x" => 40, "y" => 40, "width" => 690, "height" => 400], "option_id" => 5],
                ],
                "material_name" => "Wood",
                'material_image' => "https://bit.ly/3leXIh2",
                "code" => "code0001",
                "size" => "100W 100L 100H",
                "price" => "500",
                "offer_price" => "450",
                "quantity" => 5
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://bit.ly/3xvj5zk", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3ldjFwI", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 650, "height" => 600], "option_id" => 5],
                    ["src" => "https://bit.ly/3FWY0kj", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],
                    ["src" => "https://bit.ly/3nZ0WXB", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 490, "height" => 500], "option_id" => 5],
                ],
                "material_name" => "Metal",
                'material_image' => "https://bit.ly/3lbX5EH",
                "code" => "code0002",
                "size" => "200W 200L 200H",
                "price" => "1000",
                "offer_price" => "1450",
                "quantity" => 10
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://bit.ly/3lffoJe", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3FUSXks", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 650, "height" => 600], "option_id" => 5],
                    ["src" => "https://bit.ly/3HXlHLp", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],
                    ["src" => "https://bit.ly/3D0ytF5", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 490, "height" => 500], "option_id" => 5],
                ],
                "material_name" => "Fabric",
                'material_image' => "https://bit.ly/3p8WnJz",
                "code" => "code0003",
                "size" => "300W 300L 400H",
                "price" => "2000",
                "offer_price" => "2450",
                "quantity" => 15
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://bit.ly/3cSg037", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/2ZANShW", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 650, "height" => 600], "option_id" => 5],
                    ["src" => "https://bit.ly/317Yrd0", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],
                    ["src" => "https://bit.ly/3IbBJS7", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 490, "height" => 500], "option_id" => 5],
                ],
                "material_name" => "Velvet",
                'material_image' => "https://bit.ly/3p3MM73",
                "code" => "code0004",
                "size" => "400W 400L 400H",
                "price" => "3500",
                "offer_price" => "3450",
                "quantity" => 20
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://bit.ly/3cTFP2C", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3xz6yLA", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 650, "height" => 600], "option_id" => 5],
                    ["src" => "https://bit.ly/3HZdaYa", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],
                    ["src" => "https://bit.ly/3146C9N", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 490, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3cSg037", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 450, "height" => 500], "option_id" => 5],
                    ["src" => "https://bit.ly/3FWY0kj", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 380, "height" => 300], "option_id" => 5],

                ],
                "material_name" => "Cotton",
                'material_image' => "https://bit.ly/3CV79YN",
                "code" => "code0005",
                "size" => "500W 500L 500H",
                "price" => "4500",
                "offer_price" => "4450",
                "quantity" => 25
            ]
        ]);
        return response()->json([
            "options" => $options,
        ], 200);
    }
}
