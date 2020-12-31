<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    //
    // Todo ....
    public function AddProject(Request $request){
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count() > 0) {
            if ($request->has('company_id')) {
                $company = Company::find($request->company_id);
                if ($company !== null) {
                    if (auth()->user()->id === $company->owner[0]->pivot->user_id) {
                        return response()->json([
                            'message'=>'Loading .....',
                            'data'   =>[
                                'company__user'=>$company->owner[0]->pivot->user_id,
                                'user_id'=>auth()->user()->id
                                ]
                        ], 200);
                    }else{
                        return response()->json([
                            'successful' => '0',
                            'status'  => '02',
                            'error' => 'Not Authorized You are not company owner '
                        ], 400);
                    }
                }else{
                    return response()->json([
                        'successful' => '0',
                        'status'  => '02',
                        'error' => 'Not Authorized There is no company with this id'
                    ], 400);
                }
            }else if($request->has('store_id')){
                $createor = 'store';
                $createor_id = $request->store_id;
            }else{
                return response()->json([
                    'successful' => '0',
                    'status'  => '02',
                    'error' => 'Not Authorized'
                ], 400);
            }
        }else {
                return response()->json([
                    'successful' => '0',
                    'status'  => '02',
                    'error' => 'Not Authorized'
                ], 400);
            }

    }
}
