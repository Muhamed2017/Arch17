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
                'covers' => $product->options[0]->covers,
                'link' => 'www.arch17test.live/product/' . $id,
                'brand' => $product->stores->name
            ];
            view()->share('data', $data);
            $pdf = PDF::loadView('PDF.product', $data)->setPaper('a4', 'landscape')->setWarnings(false);
            return $pdf->download('Arch17_' . $product->identity[0]->name . '.pdf');
        }
    }



    // public function powerPoint()
    // {
    //     $objPHPPowerPoint = new PhpPresentation();
    //     // $objPHPPowerPoint->setCreator('Sketch Presentation')
    //     //     ->setLastModifiedBy('Sketch Team')
    //     //     ->setTitle('Sketch Presentation')
    //     //     ->setSubject('Sketch Presentation')
    //     //     ->setDescription('Sketch Presentation')
    //     //     ->setKeywords('office 2007 openxml libreoffice odt php')
    //     //     ->setCategory('Sample Category');
    //     $objPHPPowerPoint->removeSlideByIndex(0);
    //     // $this->slide1($objPHPPowerPoint);
    //     $this->slide2($objPHPPowerPoint);
    //     $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
    //     return $oWriterPPTX->save(__DIR__ . "/sample.pptx");
    // }



    // public function slide2(&$objPHPPowerPoint)
    // {
    //     // Create slide
    //     $currentSlide = $objPHPPowerPoint->createSlide();
    //     // Create a shape (drawing)
    //     // $shape = $currentSlide->createDrawingShape();
    //     // $shape->setName('image')
    //     //     ->setDescription('image')
    //     //     ->setPath(public_path() . '/phppowerpoint_logo.gif')
    //     //     ->setHeight(300)
    //     //     ->setOffsetX(10)
    //     //     ->setOffsetY(10);
    //     // Create a shape (text)
    //     $shape = $currentSlide->createRichTextShape()
    //         ->setHeight(300)
    //         ->setWidth(600)
    //         ->setOffsetX(170)
    //         ->setOffsetY(180);
    //     $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    //     $textRun = $shape->createTextRun('Lorem Ipsum is simply dummy text of the printing and typesetting industry.');
    //     $textRun->getFont()->setBold(true)
    //         ->setSize(16)
    //         ->setColor(new Color('FFE06B20'));
    // }
}
