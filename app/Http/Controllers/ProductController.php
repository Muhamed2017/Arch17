<?php

namespace App\Http\Controllers;

use App\Mail\requestProductMail;
use App\Models\File;
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
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use App\Models\Collection;
use App\Models\Folder;
use App\Models\Project;
use App\Models\Type;
use App\Models\UserCollection;

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
                'store' => $store,
                'brand' => $product->store


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
        $lighting_types = json_decode($request->lighting_types, true);
        $installations = json_decode($request->installations, true);
        $colorTempratures = json_decode($request->colorTempratures, true);
        $bulbTypes = json_decode($request->bulbTypes, true);
        $applied_on = json_decode($request->applied_on, true);

        $this->validate($request, [
            // step one
            'name'          => 'required|string|max:250',
            'kind'          => 'required|string|max:2000',
            'category'      => 'required|string|max:2000',
            'kind'          => 'required|string|max:250',
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
        $product_identity->kind = $request->kind;
        $product_identity->store_id = $product->store_id;
        $product_identity->places_tags = $request->places_tags;
        $product_identity->category = $request->category;
        $product_identity->country = $request->country;
        $product_identity->style = $styles;
        $product_identity->type = $types;
        $product_identity->base = $bases;
        $product_identity->shape = $shapes;


        $product_identity->lighting_types = $lighting_types;
        $product_identity->installations = $installations;
        $product_identity->colorTempratures = $colorTempratures;
        $product_identity->bulbTypes = $bulbTypes;
        $product_identity->applied_on = $applied_on;

        $product_identity->seats = $seats;
        $product_identity->material = $materials;
        $product_identity->is_outdoor = $request->is_outdoor;
        $product_identity->is_for_kids = $request->is_for_kids;
        $product_identity->product_file_kind = $request->product_file_kind;

        // $type = Type::all()->where('store_id', );
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
                    'size_w' => $request->size_w,
                    'size_l' => $request->size_l,
                    'size_h' => $request->size_h,
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

    public function UpdateOrCteateFile(Request $request, $product_id)
    {
        $identity = ProductIdentity::find($product_id);
        try {
            $file = File::updateOrCreate(
                [
                    'id' => $request->file_id,
                ],
                [
                    'product_id' => $product_id,
                    'file_name' => $request->file_name,
                    'file_type' => $request->file_type,
                    'software' => $request->software,
                    'ggldrive' => $request->ggldrive,
                    'onedrive' => $request->onedrive,
                    'dropbox' => $request->dropbox,
                    'baidu' => $request->baidu,
                ]
            );
            $identity->product_file_kind = "yes";
            $identity->save();
            return response()->json([
                'message' => 'File Added Successfully',
                'file' => $file
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
            $product_desc->mat_desc_content = $request->mat_desc_content;
        }
        if ($request->has('size_content')) {
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

        try {
            $description = ProductDescription::updateOrCreate(
                [
                    'product_id' => $id,
                ],
                [
                    'product_id' => $id,
                    'overview_content' => $request->overview_content,
                    'mat_desc_content' => $request->mat_desc_content,
                    'size_content' => $request->size_content
                ]
            );
            return response()->json([
                'message' => 'Updated Descr Successfully',
                'product_desc' => $description
            ], 200);
        } catch (Throwable $er) {
            return $er;
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
        $brand = $product->store;
        $collections = $product->collections;
        if (!$product) {
            return response()->json([
                'message' => "product not found or deleted"
            ], 404);
        }
        return response()->json([
            'product' => $product,
            'brand' => $brand,
            'collections' => $collections
        ], 200);
    }
    public function testImageUpload(Request $request, $id)
    {
        $this->validate($request, [
            'img'   => 'nullable|array',
            'img.*' => "required|mimes:jpeg,jpg,png|between:1,10000"

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
    public function uploadImgToCloud(Request $request)
    {
        $this->validate($request, [
            'cover' => 'nullable|mimes:png,jpg|between:1,20000',
        ]);
        $url = $request->cover->storeOnCloudinary()->getSecurePath();
        return response()->json([
            'src' => $url
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

    public function homeData()
    {
        $projects = Project::all();
        $recent = $projects->take(-6)->toArray();
        $groped = $projects->groupBy('kind')
            ->toArray();

        $products = ProductIdentity::latest()->where('preview_cover', '!=', null)->take(8)->get();
        $products->forget('product');
        if (!empty($products)) {
            return response()->json([
                'products' => $products,
                'recent_projects' => $recent,
                'projects_types' => $groped,

            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }

    public function filterProductSearchPage(Request $request)
    {
        $products = QueryBuilder::for(ProductIdentity::class)
            ->allowedFilters([
                AllowedFilter::exact('category'),
                AllowedFilter::exact('is_outdoor'),
                AllowedFilter::exact('is_for_kids'),
                AllowedFilter::exact('product_file_kind'),
                AllowedFilter::exact('kind'),
                'type', 'seats', 'base', 'shape', 'style', 'material', 'lighting_types', 'installations',
                'bulbTypes', 'colorTempraturs', 'applied_on',
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


    public function requestProduct(Request $request, $id)
    {
        $this->validate($request, [
            'email'          => 'required|email',
            'phone'          => 'required|string|max:255',
            'type'          => 'required|string|max:255',
            'message'          => 'required|string',
            'product_name'          => 'required|string',
            'product_image'          => 'required|string',
            'brand_name'          => 'required|string',
        ]);
        try {
            // Mail::to('mgm564441@gmail.com')->send(new requestProductMail(
            Mail::to('sales@arch17.co')->send(new requestProductMail(
                $id,
                $request->product_name,
                $request->brand_name,
                $request->type,
                $request->email,
                $request->phone,
                $request->message,
                $request->product_image
            ));
            return response()->json([
                'status' => 1,
                'message' => "Recieved Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'status' => 0,
                'error' => $err,
                'message' => "Error Occur !"
            ], 500);
        }
    }

    // user collection (Folder) create api..
    public function makeNewCollection(Request $request)
    {

        $this->validate($request, [
            'user_id'          => 'required|string',
            'name'          => 'required|string',
        ]);

        $folder = new Folder();
        $folder->user_id = $request->user_id;
        $folder->name = $request->name;

        if ($folder->save()) {
            return response()->json([
                'success' => true,
                'folder' => $folder,
                'message' => "Collection Created Successfully"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs, Try Again"
            ], 500);
        }
    }

    public function saveToFolder(Request $request)
    {
        $this->validate($request, [
            'product_id'          => 'required|string',
            'folder_id'          => 'required|string',
        ]);

        $product = Product::find($request->product_id);
        $folder = Folder::find($request->folder_id);
        try {
            $product->folders()->attach($folder);
            return response()->json([
                'success' => true,
                'message' => "Saved to collection Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs",
                'error' => $err
            ], 500);
        }
    }

    public function removerFromFolder(Request $request)
    {
        $this->validate($request, [
            'product_id'          => 'required|string',
            'folder_id'          => 'required|string',
        ]);

        $product = Product::find($request->product_id);
        $folder = Folder::find($request->folder_id);
        try {
            $product->folders()->detach($folder);
            return response()->json([
                'success' => true,
                'message' => "Removed from collection Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs",
                'error' => $err
            ], 500);
        }
    }

    public function allFolders()
    {
        $folders = Folder::all();

        if (!empty($folders)) {
            return response()->json([
                'products' => $folders,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }


    public function listAllFolders($id)
    {


        $folders = Folder::with('products')->where('user_id', $id)->get();

        if (!empty($folders)) {
            return response()->json([
                'products' => $folders,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }

    public function listAllFoldersByProduct($user_id, $product_id)
    {

        $_saved_in = [];
        $_unsaved = [];
        $folders = Folder::all()->where('user_id', $user_id);
        $product = Product::find($product_id);
        foreach ($folders as $folder) {
            if ($folder->products->contains($product)) {
                array_push($_saved_in, ['name' => $folder->name, 'id' => $folder->id, 'saved' => true]);
            } else {
                array_push($_unsaved, ['name' => $folder->name, 'id' => $folder->id, 'saved' => false]);
            }
        }
        if (!empty($folders)) {
            return response()->json([
                // $folders => $_unsaved . c
                'saved_in' => $_saved_in,
                'unsaved_in' => $_unsaved
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Folders Added! ',
            ], 200);
        }
    }



    /// Brand Collection apis
    public function newBrandColelction(Request $request)
    {
        $this->validate($request, [
            'store_id'          => 'required|string',
            'product_id'          => 'required|string',
            'collection_name'          => 'required|string',
        ]);

        $product = Product::find($request->product_id);
        $collection = new Collection();
        $collection->store_id = $request->store_id;
        $collection->collection_name = $request->collection_name;

        if ($collection->save()) {
            try {
                $collection->products()->attach($product);
                return response()->json([
                    'success' => true,
                    'message' => "Product Placed to collection Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => "Error" . $err . " Occured"
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => "Error Occurs, Try Again"
        ], 500);
    }


    public function attachProductToBrandCollection(Request $request)
    {
        $this->validate($request, [
            'product_id'          => 'required|string',
            'collection_id'      => "required|string"
        ]);

        $product = Product::find($request->product_id);
        $collection = Collection::find($request->collection_id);

        if ($collection && $product) {
            try {
                $collection->products()->attach($product);
                return response()->json([
                    'success' => true,
                    'message' => "Product Placed to collection Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => "Error" . $err . " Occured"
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => "Product or Collection Not found or deleted"
        ], 404);
    }

    public function deAttachProductToBrandCollection(Request $request)
    {
        $this->validate($request, [
            'product_id'          => 'required|string',
            'collection_id'      => "required|string"
        ]);


        $product = Product::find($request->product_id);
        $collection = Collection::find($request->collection_id);

        if ($collection && $product) {
            try {
                $collection->products()->detach($product);
                return response()->json([
                    'success' => true,
                    'message' => "Product Removed from collection Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => "Error" . $err . " Occured"
                ], 500);
            }
        }
        return response()->json([
            'success' => false,
            'message' => "Product or Collection Not found or deleted"
        ], 404);
    }


    public function getCollectionById($id)
    {

        $collection = Collection::find($id);
        return $collection;
    }

    public function getUserCollections($id)
    {
        $collections = Collection::all()->where('user_id', $id);

        if (nullOrEmptyString($id)) {
            return response()->json([
                'message' => 'unknown user'
            ], 200);
        }
        return response()->json([
            'collections' => $collections
        ], 200);
    }

    public function searchBar()
    {
        $products = ProductIdentity::all();
        $brands = Store::all();

        return response()->json([
            'products' => $products,
            'brands' => $brands
        ], 200);
    }


    public function testDeleteRelated()
    {
        $products = Product::all();
        $identities = ProductIdentity::all();
        $options = Option::all();
        $files    = File::all();
        $desc = ProductDescription::all();
        $galleries = ProductGallery::all();


        return response()->json([
            'products' => $products,
            'identities' => $identities,
            'options' => $options,
            'files' => $files,
            'desc' => $desc,
            'galleries' => $galleries,
        ], 200);
    }





    // delete apis
    //---------------------------------------------

    //delete product option by option id
    public function deleteOption($id)
    {
        $option = Option::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option Not fount or already deleted '
            ], 404);
        }
        try {
            $option->delete();
            return response()->json([
                'success' => true,
                'message' => "Option deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }

    // delete product file by file id
    public function deleteFile($id)
    {
        $file = File::find($id);
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File Not fount or already deleted '
            ], 404);
        }
        try {
            $file->delete();
            return response()->json([
                'success' => true,
                'message' => "File deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }


    public function deleteGallery($id)
    {
        $gallery = ProductGallery::find($id);
        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery Not fount or already deleted '
            ], 404);
        }
        try {
            $gallery->delete();
            return response()->json([
                'success' => true,
                'message' => "Gallery deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }

    //delete product (with all it's related models .. identity, options, description, galleries, files)
    public function deleteProduct($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not fount or already deleted '
            ], 404);
        }
        try {
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => "Product deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }


    //delete brand by it's Id
    public function deleteBrand($id)
    {
        $brand = Store::find($id);
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'brand Not fount or already deleted '
            ], 404);
        }
        try {
            $brand->delete();
            return response()->json([
                'success' => true,
                'message' => "brand deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }
}
