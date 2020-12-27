<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Support\Services\AddImagesToEntity;

class CompanyController extends Controller
{
    /**
     *      hepler functions to check if logged in user is company owner
     *      check if company id is in user->compnay array
     *      user->compnanies is object so i convert it to array to be able to use array_cloumn funciton
     *      user in [
     *      1- update_company_info
     *      ]
     */
    private function isOwner($id){
        return in_array($id, array_column( current((array)auth()->user()->companies) ,'id'));
    }



    /**
     *      create new company function
     *      1- check if user is  logged in
     *      2- validate comming data
     *      3- prepare data in data array
     *      4- create new company
     *      5- add company in company_user tabel for relation
     *      6- resluggify the slug column to be null before requesting a slud arrtibute
     *
     */
    public function save_company(Request $request)
    {
        if (auth()->user()) {
            $validated = $request->validate([
                'name' => 'required',
                'types' => 'required|array|distinct',
                'country'=>  'required',
                'city'=> 'required',
                'email' => 'required|email:rfc,dns'
            ]);
            $data = [
                'name'     => $request->name,
                'types'    => $request->types,
                'country'  =>$request->country,
                'city'     =>$request->city,
                'email'    => $request->email
            ];
            try{
                $company = new Company($data);
                auth()->user()->companies()->save($company);
                // resluggify
                $company->slug = null;
                $company->save();
                return response()->json([
                'data' => array($data,auth()->user()->companies),
                'status'=>402
                ]);
            }catch(\Illuminate\Database\QueryException $error_message_sql) {
                return response()->json([
                    'data' => $data,
                    'report'=> $error_message_sql->getMessage(),
                    'status'=>500
                ]);
            }
        }else{
            return response()->json([
                'successful' => '0',
                'status'  => '02',
                'error' => 'not authorized '
            ], 400);
        }
    }
    /**
     *      create new company function
     *      1- check if thte logged in user is owner by hepler function isOwner
     *      2- validate comming data
     *      3- prepare data in data array
     *      4- resluggify the slug column to be null before requesting a slud arrtibute
     *      5- update the company
     *      NOTE : Becaous We Now Using Slugable packege
     *      to generate urls we will not allow user to update his designer page name
     */
    public function update_company_info(Request $request)
    {
        if ($this->isOwner($request->company_id)) {
            $validated = $request->validate([
                'types' => 'required|array|distinct',
                'country'=>  'required',
                'city'=> 'required',
                'email' => 'required|email:rfc,dns',
                'name'=>'required'
            ]);
            $data = [
                'types'    => $request->types,
                'country'  =>$request->country,
                'city'     =>$request->city,
                'email'    => $request->email,
                'name'    => $request->name,
                'id'       => $request->company_id
            ];
            try{
                // Company::where('id',$data['id'])->update($data);
                $company = Company::find($request->company_id);
                $company->name = $request->name;
                $company->types = $request->types;
                $company->city  = $request->city;
                $company->country = $request->country;
                $company->email = $request->email;
                // resluggify
                $company->slug = null;
                $company->save();
                return response()->json([
                'successful' => '1',
                'status' => '01',
                'message' => 'Your comapny has been updated successfully',
                'data' =>$company,
                ]);
            }catch(\Illuminate\Database\QueryException $error_message_sql) {
                return response()->json([
                    'successful' => '0',
                    'status'  => '02',
                    'error' => 'failed, please try again',
                    'report'=> $error_message_sql->getMessage(),
                ],500);
            }
        }else {
            return response()->json([
                'successful' => '0',
                'status'  => '02',
                'error' => 'not authorized ',
                'ids' => array_column( current((array)auth()->user()->companies) ,'id'),
                'user' =>auth()->user()
            ], 400);
        }
    }
    /**
     *      create new company function
     *      1- check if thte logged in user is owner by hepler function isOwner
     *      2- prepare data in data array
     *      3- update the company
     *
     *      NOTE : Iam Not Validate the data becaous it can be nullable
     */
    public function update_company_profile(Request $request)
    {
        if ($this->isOwner($request->company_id)) {
            $data = [
                'about'     => $request->about,
                'website'   => $request->website,
                'phone'     =>$request->phone,
                'id'       => $request->company_id
            ];
            try{
                Company::where('id',$data['id'])->update($data);
                return response()->json([
                'successful' => '1',
                'status' => '01',
                'message' => 'Your comapny has been updated successfully',
                'data' => Company::where('id',$data['id'])->get(),
                ]);
            }catch(\Illuminate\Database\QueryException $error_message_sql) {
                return response()->json([
                    'successful' => '0',
                    'status'  => '02',
                    'error' => 'failed, please try again',
                    'report'=> $error_message_sql->getMessage(),
                ],500);
            }
        }else {
            return response()->json([
                'successful' => '0',
                'status'  => '02',
                'error' => 'not authorized ',
                'ids' => array_column( current((array)auth()->user()->companies) ,'id'),
                'user' =>auth()->user()
            ], 400);
        }
    }


    /**
     *
     *
     *
     * */
    public function get_company($slug)
    {
        $company = Company::findOrFail($slug );
        return response()->json([
            'successful' => '1',
            'status' => '01',
            'message' => 'Your comapny is  here',
            'data' => $company,
            'owner' => $this->isOwner($company->id)
            ]);
    }

    public function upload_designer_avatar(Request $request)
    {
        if ($this->isOwner($request->company_id)) {
            $company = Company::find($request->company_id );
            if ($request->hasFile('avatar')) {
                (new AddImagesToEntity($request->avatar, $company, ["width" => 600] ))->execute() ;
                return response()->json($company->images);
            }
        }
    }
}
