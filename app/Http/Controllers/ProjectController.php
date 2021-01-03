<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectDescription;
use App\Models\ProjectDesigner;
use App\Models\ProjectSupplier;
use App\Support\Services\AddImagesToEntity;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * 1- check if login 
     * 2- check if allow to add project 
     * 3- get the author of adding project like user or store or company
     * 4- prepare project data 
     * 5- prepare supplier data 
     * 6- prepare designer data 
     * 7- validate all data 
     * 8- save project data 
     * 9- save project suppliers data 
     * 10- save project designers data 
     * 11- append the project in the response 
    */
    // Todo ....
    public function AddProject(Request $request){
        #2- check if allow to add project 
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0) {
            #3- get the author of adding project like user or store or company
            if ($request->has('store_id')) {
                $author_id = $request->store_id;
                $this->validate($request, $this->addValidationRules());
                $projectData = $this->addProjectData($request);
                $projectData['authorable_id'] = $author_id ;
                $projectData['authorable_type'] = Store::class;
                $project = new Project($projectData);
                if ($project->save()) {
                    return response()->json([
                        'message'=>'Loading .....',
                        'data'   =>[
                            'auth()->user()->companies'=>auth()->user()->companies,
                            'user_id'=>auth()->user()->id,
                            'author'=>['store', $author_id],
                            'data'=>['data',$projectData],
                            '$project'=>$project,
                            'store_projects'=> Store::find($author_id)->projects,
                            'suppliers'=>$suppliers_array
                            ]
                    ], 200);
                }
            }else if($request->has('company_id')){
                $author_id = $request->company_id;
                $this->validate($request, $this->addValidationRules());
                $projectData = $this->addProjectData($request);
                $projectData['authorable_id'] = $author_id ;
                $projectData['authorable_type'] = Company::class;
                $project = new Project($projectData);
                if ($project->save()) {
                    return response()->json([
                        'message'=>'Loading .....',
                        'data'   =>[
                            'auth()->user()->companies'=>auth()->user()->companies,
                            'user_id'=>auth()->user()->id,
                            'author'=>'company',[$author_id],
                            'data'=>['data',$projectData],
                            '$project'=>$project,
                            'company_projects'=> Company::find($author_id)->projects
                            ]
                    ], 200);               
                }
            }else if ($request->has('author_id')) {
                $author_id = $request->author_id;
                $this->validate($request, $this->addValidationRules());
                $projectData = $this->addProjectData($request);
                $projectData['authorable_id'] = $author_id ;
                $projectData['authorable_type'] = User::class;
                $project = new Project($projectData);
                if ($project->save()) {
                    return response()->json([
                        'message'=>'Loading .....',
                        'data'   =>[
                            'auth()->user()->user'=>auth()->user()->projects,
                            'user_id'=>auth()->user()->id,
                            'author'=>'User',[$author_id],
                            'data'=>['data',$projectData],
                            '$project'=>$project,
                            'company_projects'=> User::find($author_id)->projects
                            ]
                    ], 200);               
                }
            }
        }else{
            return response()->json([
                'message'=>'not allow to ad project'
            ], 400);
        }
    }

    public function addValidationRules($id = ''){
        return [
            'name' => 'required|string|max:250',
            'cover_name' => 'required|string|max:250',
            'category' => 'required|string|max:2000',
            'country' => 'required|string|max:250',
            'city' => 'required|string|max:250',
            'year' => 'required|string|max:250',
            'types' => 'required|array',
            'text_description' => 'nullable|array',
            'text_description.*' => 'nullable|string|max:250',
        ];
    }
    public function addProjectData(Request $request)
    {
        $input = $request->only(
            'name', 'types', 'country', 'city', 'text_description', 'category','cover_name','year'
        );
        return $input;
    }
    // public function addProjectSupplier(Request $request)
    // {
    //     $suppliersarray = [];
    //     foreach ($request->suppliers as $key => $value) {
    //         array_push($suppliersarray , [
    //             'store_id'   => 2,
    //             'project_id' => 5
    //         ]);
    //     }
    //     return $suppliersarray;
    // }

    /**
     * functions 
     * 1- if allow to add project
     * 2- add project info 
     * 3- add project description 
     * 4- add project roles
     * 5- add project cover
     * 6- get proejct author
     * 7- validation info 
     * 8- validation description 
     * 9- validation roles
     * 10- validation cover
     * 11- respones function
     */ 

    public function isAuthor()
    {
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project === 1) {
            return ;
        }else{
            return false;
        }
    }
    public function getAuthor(Request $request)
    {
        if ($request->has('store_id')) {
            return 
                [
                    'author'    => Store::class,
                    'author_id' => $request->store_id
                ];
        }elseif ($request->has('company_id')) {
            return
                [
                    'author'    => Company::class,
                    'author_id' => $request->company_id
                ];
        }elseif ($request->has('user_id')) {
            return 
                [
                    'author'    => User::class,
                    'author_id' => $request->user_id
                ];
        }else {
            return
                [
                    'author'    => false,
                    'author_id' => false
                ];
        }
    }
    public function returnProjectResponse($data = array() , $status)
    {
        return response()->json([
            'user' => auth()->user()->email,
            'data' => $data
        ], $status);
    }
    // 1- add info  
    // 1-1 add info validation 
    public function addInfoValidationRules($id = ''){
        return [
            'name' => 'required|string|max:250',
            'category' => 'required|string|max:2000',
            'country' => 'required|string|max:250',
            'city' => 'required|string|max:250',
            'year' => 'required|string|max:250',
            'types' => 'required|array',
            'types.*' => 'required|string',
        ];
    }
    // 1-2 prepare project data 
    public function addProjectInfoData(Request $request)
    {
        $input = $request->only(
            'name', 'types', 'country', 'city', 'category','year'
        );
        return $input;
    }
    // 1-3 save project data 
    public function addProjectInfo(Request $request)
    {    
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project !== 0) {
            $this->validate($request, $this->addInfoValidationRules());
            $author = $this->getAuthor($request);
            $info = $this->addProjectInfoData($request);
            $project = new Project();
            $project->name      = $request->name;
            $project->category  = $request->category;
            $project->types     = $request->types;
            $project->country   = $request->country;
            $project->city      = $request->city;
            $project->year      = $request->year;
            $project->authorable_id = $author['author_id'] ;
            $project->authorable_type = $author['author'] ;
            if ($project->save()) {
                $response = [
                    'project'   =>$project,
                    'author'    =>$project->authorable_type,
                    '1'    =>auth()->user()->stores->count(),
                    '2'    =>auth()->user()->companies->count(),
                    '3'    =>auth()->user()->allow_to_add_project
                ];
                return  $this->returnProjectResponse($response  , 200 );
            }
        }else{
            return false;
        }

    }



    // 2- add description
    // 2-1 description validation
    public function addDescriptionValidationRules($id = ''){
        $rx = '#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si';
        return [
            'description_text'    => 'nullable|array',
            'description_text.*'  => 'required|string',
            'description_media'   => 'nullable|array',
            'description_media.*' => 'required|image|mimes:jpeg,bmp,jpg,png|between:1,6000',
            'video'   => 'nullable|array',
            'video.*' => "required|string|regex:$rx"
        ];
    }
    // 2-3 save description 
    public function addProjectDescription(Request $request)
    {
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project !== 0) {
            $this->validate($request, $this->addDescriptionValidationRules());
            $project = Project::findOrFail($request->project_id);
            $project_description = new ProjectDescription();    
            $project_description->project_id = $project->id;
            $project_description->description_text = $request->description_text;
            if ($project->description) {
                $response = [
                    'message' => 'project has already a description',
                    'project_description' => $project->description    
                ] ;
                return  $this->returnProjectResponse($response , 409 );
            }
            if ($project_description->save()) {
                if ($request->hasFile('description_media')) (new AddImagesToEntity($request->description_media, $project_description, ["width" => 1024]))->execute();
                $response =[
                    'message' => 'description attached to project successfully',
                    'project_description' => Project::find($request->project_id)->description
                ] ;
                return  $this->returnProjectResponse($response , 200 );
            }
        }else{
            return false;
        }

    }


    //3- add project suppliers
    // 3- validation rolles
    public function addSupplierValidationRules($id = ''){
        return [
            'store_id' => 'required',
        ];
    }
    public function addProjectSupplier(Request $request)
    {
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project !== 0) {
            $this->validate($request, $this->addSupplierValidationRules());
            $project_supplier = new ProjectSupplier();
            $project = Project::findOrFail($request->project_id);
            $project_supplier->project_id = $request->project_id;
            $project_supplier->store_id = $request->store_id;
    
            if ($project_supplier->save()) {
                return response()->json([
                    'message' => 'supplier attached to product successfully',
                    'supliers' => Project::findOrFail($request->project_id)->suppliers
                ], 200);
            }
        }else{
            return false;
        }

    }


    //3- add project designer
    // 3- validation rolles
    public function addDesignerValidationRules($id = ''){
        return [
            'user_id' => 'required',
        ];
    }
    public function addProjectDesigner(Request $request)
    {
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project === 1) {
            return ;
        }else{
            return false;
        }
        $this->validate($request, $this->addDesignerValidationRules());
        $project_designer = new ProjectDesigner();
        $project = Project::findOrFail($request->project_id);
        $project_designer->project_id = $request->project_id;
        $project_designer->user_id = $request->user_id;
        if ($project_designer->save()) {
            return response()->json([
                'message' => 'designer attached to product successfully',
                'designers' => Project::findOrFail($request->project_id)->designers
            ], 200);
        }
    }


    // 4- add designer cover
    public function addProjectCover(Request $request)
    {
        if (auth()->user()->stores->count() > 0 || auth()->user()->companies->count()>0 || auth()->user()->allow_to_add_project !== 0) {
            $project = Project::find($request->project_id);
            $project->cover_name = $request->cover_name;
            $project->save();
            if ($request->hasFile('cover')) (new AddImagesToEntity($request->cover,$project, ["width" => 1024]))->execute();
            return response()->json([
                'message' => 'project cover saved ',
                'project' => Project::findOrFail($request->project_id)->images
            ], 200);
        }else{
            return false;
        }

    }


}
