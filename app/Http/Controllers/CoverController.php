<?php

namespace App\Http\Controllers;

use App\Models\Cover;
use App\Models\Product;
use App\Models\ProductIdentity;
use App\Models\ProductOptions;
use Illuminate\Http\Request;
use Throwable;
use PDF;

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


    public function testPDF($id)
    {
        $product = Product::find($id);
        if ($product) {
            $data = [
                'id' => $product->id,
                'name' => $product->identity[0]->name,
                'kind' => $product->identity[0]->kind,
                'image' => $product->options[0]->covers[0]['src'],
                'link' => 'www.arch17test.live/product/' . $id,
                'brand' => $product->stores->name
            ];
            view()->share('data', $data);
            $pdf = PDF::loadView('PDF.product', $data);
            return $pdf->download('Arch17_product_' . $id . 'pdf');
            // return $pdf->stream();
            // return $product;
        }

        // view()->share('data', $data);

        // $pdf = PDF::loadView('PDF.product', $data);
        // // return $pdf->download('pdf_file.pdf');
        // return $pdf->stream();
        // return $data;
    }
}
