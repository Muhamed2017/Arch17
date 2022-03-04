<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use PDF;


class SubscriptionController extends Controller
{
    //
    /**
     * mohamed mahmoud 24-12-2020 | 1:55pm
     * create subscriotion
     * email,profession,name : requierd
     * other data can be null
     * status 402 for success
     * status 500 for server error
     * Illuminate\Database\QueryException for excepet query error
     * and that should be ignoder in the deploymen.
     *
     */
    function create(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|unique:subscriptions|email:rfc,dns',
            'profession' => 'required',
            'name' => 'required',
        ]);
        $data = [
            'email' => $request->email,
            'name' => $request->name,
            'profession' => $request->profession,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address
        ];
        try {
            Subscription::create($data);
            return response()->json([
                'data' => $data,
                'status' => 402
            ]);
        } catch (\Illuminate\Database\QueryException $error_message_sql) {
            return response()->json([
                'data' => $data,
                'report' => $error_message_sql->getMessage(),
                'status' => 500
            ]);
        }
    }
    public function testPDF($id)
    {
        $product = Product::find($id);
        if ($product) {
            $data = [
                'id' => $product->id,
                'name' => $product->identity[0]->name,
                'kind' => $product->identity[0]->kind,
                'image' => $product->options[0]->covers[0]['src'],
                'covers' => $product->options[0]->covers,
                'link' => 'www.arch17test.live/product/' . $id,
                'brand' => $product->stores->name
            ];
            view()->share('data', $data);
            $pdf = PDF::loadView('PDF.product', $data)->setPaper('a4', 'landscape')->setWarnings(false);
            return $pdf->download('Arch17_' . $product->identity[0]->name . '.pdf');
        }
    }
}
