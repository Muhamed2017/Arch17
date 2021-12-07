<?php

namespace App\Http\Controllers;

use App\Models\Option as ModelsOption;
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
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;
use App\Models\Option;
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
        $materials = json_decode($request->material, true);
        $seats = json_decode($request->seats, true);
        $bases = json_decode($request->bases, true);
        $shapes = json_decode($request->shape, true);
        $types = json_decode($request->type, true);
        $styles = json_decode($request->style, true);

        $this->validate($request, [
            // step one
            'name'          => 'required|string|max:250',
            'kind'          => 'required|string|max:2000',
            'category'      => 'required|string|max:2000',
            // 'type'          => 'nullable|array',
            // 'type.*'          => 'nullable|string|max:2000',
            // 'style'         => 'nullable|array',
            // 'style.*'         => 'nullable|string|max:2000',
            'kind'          => 'required|string|max:250',
            // 'material'      => 'nullable|array',
            // 'material.*'      => 'nullable|string|max:250',
            // 'base'      =>     'nullable|array',
            // 'base.*'      =>     'nullable|string|max:250',
            // 'seats'      =>     'nullable|array',
            // 'seats.*'      =>     'nullable|string|max:250',
            // 'shape'      =>     'nullable|array',
            // 'shape.*'      =>     'nullable|string|max:250',
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
        $product_identity->category = $request->category;
        $product_identity->country = $request->country;
        // $product_identity->style = $request->style;
        // $product_identity->type = $request->type;
        // $product_identity->base = $request->base;
        // $product_identity->shape = $request->shape;
        // $product_identity->seats = $request->seats;
        // $product_identity->material = $request->material;

        $product_identity->style = $styles;
        $product_identity->type = $types;
        $product_identity->base = $bases;
        $product_identity->shape = $shapes;
        $product_identity->seats = $seats;
        $product_identity->material = $materials;
        $product_identity->is_outdoor = $request->is_outdoor;
        $product_identity->is_for_kids = $request->is_for_kids;
        $product_identity->product_file_kind = $request->product_file_kind;
        if ($product_identity->save()) {
            return response()->json([
                'message' => 'product_identity created, ready to add option and price',
                'identity' => $product_identity,
                'tab_index' => 1
            ], 200);
            // }
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

    public function UpdateOrCreateOption(Request $request, $product_id)
    {

        $covers = json_decode($request->covers, true);
        try {
            $option = Option::updateOrCreate(
                [
                    'id' => $request->option_id,
                ],
                [
                    'product_id' => $product_id,
                    'material_name' => $request->material_name,
                    'material_image' => $request->material_image,
                    'price' => $request->price,
                    'offer_price' => $request->offer_price,
                    'quantity' => $request->quantity,
                    'code' => $request->code,
                    'size' => $request->size,
                    'covers' => $covers
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
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/18/17/20/living-room-1835923__480.jpg", "cover_id" => 5, "cropping_data" => ["x" => 0, "y" => 0, "width" => 250, "height" => 250], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2015/10/20/18/57/furniture-998265__340.jpg", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 100, "height" => 100], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2014/08/11/21/39/wall-416060__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 0, "y" => 55, "width" => 220, "height" => 110], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2017/03/19/01/43/living-room-2155376__340.jpg", "cover_id" => 35, "cropping_data" => ["x" => 50, "y" => 10, "width" => 100, "height" => 215], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2017/01/07/17/48/interior-1961070__340.jpg", "cover_id" => 45, "cropping_data" => ["x" => 0, "y" => 80, "width" => 290, "height" => 350], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2014/09/15/21/46/couch-447484__340.jpg", "cover_id" => 55, "cropping_data" => ["x" => 40, "y" => 40, "width" => 280, "height" => 400], "option_id" => 5],
                ],
                "material_name" => "Wood",
                'material_image' => "https://cdn.pixabay.com/photo/2014/07/24/02/14/polka-400704__340.png",
                "code" => "code0001",
                "size" => "100W 100L 100H",
                "price" => "500",
                "offer_price" => "450",
                "quantity" => 5
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/21/12/59/couch-1845270__340.jpg", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 150, "height" => 200], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2018/01/26/08/15/dining-room-3108037__340.jpg", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 200, "height" => 200], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2020/05/25/17/54/library-5219747__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 250, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/19/15/50/chair-1840011__340.jpg", "cover_id" => 35, "cropping_data" => ["x" => 0, "y" => 250, "width" => 80, "height" => 220], "option_id" => 5],
                ],
                "material_name" => "Metal",
                'material_image' => "https://cdn.pixabay.com/photo/2012/12/27/19/40/architecture-72808__340.jpg",
                "code" => "code0002",
                "size" => "200W 200L 200H",
                "price" => "1000",
                "offer_price" => "1450",
                "quantity" => 10
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://cdn.pixabay.com/photo/2020/05/29/15/31/lantern-5235537_960_720.jpg", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 150, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2020/08/25/18/28/workplace-5517744__340.jpg", "cover_id" => 15, "cropping_data" => ["x" => 0, "y" => 35, "width" => 290, "height" => 365], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2017/08/01/12/43/kitchen-2565105__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 0, "width" => 200, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2014/07/10/17/17/bedroom-389254__340.jpg", "cover_id" => 35, "cropping_data" => ["x" => 0, "y" => 120, "width" => 280, "height" => 350], "option_id" => 5],
                ],
                "material_name" => "Fabric",
                'material_image' => "https://cdn.pixabay.com/photo/2017/12/23/20/45/wall-3035971__340.jpg",
                "code" => "code0003",
                "size" => "300W 300L 400H",
                "price" => "2000",
                "offer_price" => "2450",
                "quantity" => 15
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://cdn.pixabay.com/photo/2012/04/13/21/32/rocking-horse-33719__340.png", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 192, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/22/19/11/brick-wall-1850095__340.jpg", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 35, "width" => 300, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2018/03/08/18/13/chair-3209341__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 280, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2015/06/24/16/36/home-820389__340.jpg", "cover_id" => 35, "cropping_data" => ["x" => 50, "y" => 0, "width" => 190, "height" => 400], "option_id" => 5],
                ],
                "material_name" => "Velvet",
                'material_image' => "https://cdn.pixabay.com/photo/2016/02/09/00/37/wood-1188082__340.jpg",
                "code" => "code0004",
                "size" => "400W 400L 400H",
                "price" => "3500",
                "offer_price" => "3450",
                "quantity" => 20
            ],
            [
                'option_id' => 5,
                'covers' => [
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/19/13/06/bed-1839184__340.jpg", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 210, "height" => 240], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2016/11/29/01/24/dog-1866530__340.jpg", "cover_id" => 15, "cropping_data" => ["x" => 10, "y" => 15, "width" => 250, "height" => 310], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2016/09/22/11/55/kitchen-1687121__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 190, "y" => 55, "width" => 100, "height" => 300], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2018/06/14/21/15/the-interior-of-the-3475656__340.jpg", "cover_id" => 35, "cropping_data" => ["x" => 150, "y" => 250, "width" => 100, "height" => 200], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2015/12/05/23/16/office-1078869__340.jpg", "cover_id" => 5, "cropping_data" => ["x" => 5, "y" => 25, "width" => 150, "height" => 250], "option_id" => 5],
                    ["src" => "https://cdn.pixabay.com/photo/2016/01/26/11/09/design-1162241__340.jpg", "cover_id" => 25, "cropping_data" => ["x" => 90, "y" => 55, "width" => 190, "height" => 300], "option_id" => 5],

                ],
                "material_name" => "Cotton",
                'material_image' => "https://cdn.pixabay.com/photo/2015/01/29/16/34/background-616360__340.jpg",
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
