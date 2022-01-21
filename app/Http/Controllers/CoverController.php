<?php

namespace App\Http\Controllers;

use App\Models\Cover;
use App\Models\Product;
use App\Models\ProductIdentity;
use App\Models\ProductOptions;
use Illuminate\Http\Request;
use Throwable;
use PDF;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use Image;
use Cloudinary;
use Faker\Core\Number;
// use Cloudinary\Cloudinary as CloudinaryCloudinary;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\File;


class CoverController extends Controller
{

    public function uploadCover(Request $request)
    {
        $this->validate($request, [
            'cover' => 'nullable|mimes:png,jpg|between:1,10000',
            'size' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'crop_data_x' => 'numeric|nullable',
            'crop_data_y' => 'numeric|nullable',
            'crop_data_w' => 'numeric|nullable',
            'crop_data_h' => 'numeric|nullable',
        ]);

        // $image = (string) Image::make($request->cover)->crop(100, 100, 100, 100)->encode();

        $cover = new Cover();
        // $cover->option_id = $request->option_id;
        $cover->original = $request->cover->storeOnCloudinary()->getSecurePath();
        $cover->cropped = cloudinary()->upload($request->file('cover')->getRealPath(), [
            'folder' => 'covers',
            'transformation' => [
                'height' => $request->crop_data_h,
                'x' => $request->crop_data_x,
                'y' => $request->crop_data_y,
                'width' => $request->crop_data_w,
                'crop' => 'crop'
            ]
        ])->getSecurePath();
        $cover->width = 1500;
        $cover->height = 1000;
        $cover->crop_data = $request->crop_data;
        $cover->size = $request->size;

        if ($cover->save()) {
            return response()->json([
                'message' => "Cover Has been uploaded Successfull",
                'cover' => $cover,
            ], 201);
        } else {
            return response()->json([
                'message' => "Something Went Wrong, Try again",
            ], 500);
        }
    }


    public function attachCoversToNewOption()
    {
        $option = new ProductOptions();
        try {
            $option->covers()->push([
                [
                    'src' => "Some Src",
                    'cropping_data' => ["ss" => "ss", "sss" => "Ssss"],
                ],
                [
                    'src' => "Another Src",
                    'cropping_data' => ["ll" => "ll", "lll" => "Llll"],
                ],

            ]);
            return response()->json([
                'message' => "covers has been added to new option",
                // 'data' => $option
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "Somethimg went wrong",
                'error' => $e
            ], 500);
        }
    }
    // public function createNewOption



}
