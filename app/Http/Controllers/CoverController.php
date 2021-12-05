<?php

namespace App\Http\Controllers;

use App\Models\Cover;
use App\Models\Product;
use App\Models\ProductOptions;
use Illuminate\Http\Request;
use Throwable;

class CoverController extends Controller
{
    public function uploadCover(Request $request)
    {
        $this->validate($request, [
            'cover' => 'nullable|mimes:png,jpg|between:1,10000',
        ]);
        $cover = new Cover();
        $cover->src = $request->cover->storeOnCloudinary()->getSecurePath();
        if ($cover->save()) {
            return response()->json([
                'message' => "Cover Has been uploaded Successfull",
                'cover' => $cover
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
