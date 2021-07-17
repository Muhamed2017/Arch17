<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Image as ImageEntity;
use App\Models\Store;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectDescription;
use App\Models\ProjectDesigner;
use App\Models\ProjectSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image as ImageUploader;
class ProjectController extends Controller
{
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

    private function isAllowedToAddProject()
    {
        if (auth()->user()->stores->count() <= 0 || auth()->user()->companies->count() <= 0 || auth()->user()->allow_to_add_project === 0) {
            return true;        
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
            if (!$this->isAllowedToAddProject()) {
                return $this->returnProjectResponse(['message'=>array(auth()->user()->stores->count(),auth()->user()->companies->count())]  , 200 );
            }
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
            $project->state      = $request->state;
            $project->authorable_id = $author['author_id'] ;
            $project->authorable_type = $author['author'] ;
            if ($project->save()) {
                $response = [
                    'project'   =>$project
                    // 'author'    =>$project->authorable_type,
                    // '1'    =>auth()->user()->stores->count(),
                    // '2'    =>auth()->user()->companies->count(),
                    // '3'    =>auth()->user()->allow_to_add_project
                ];
                return  $this->returnProjectResponse($response  , 200 );
            }
    }



    // 2- add description
    // 2-1 description validation
    public function addDescriptionValidationRules($id = ''){
        $rx = '#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si';
        return [
            'description'    => 'nullable|array',
            'description.*'  => 'required|string',
        ];
    }
    // 2-3 save description 
    public function addProjectDescription(Request $request)
    {
        if (!$this->isAllowedToAddProject()) {
            return $this->returnProjectResponse(['message'=>'not alllow to add project']  , 200 );
        } 
            $this->validate($request, $this->addDescriptionValidationRules());
            $project = Project::findOrFail($request->project_id);
            $project_description = new ProjectDescription();    
            $project_description->project_id = $project->id;
            $project_description->description_text = $request->description;
            if ($project->description) {
                $response = [
                    'message' => 'project has already a description',
                    'project_description' => $project->description    
                ] ;
                return  $this->returnProjectResponse($response , 409 );
            }
            if ($project_description->save()) {
                // if ($request->hasFile('description_media')) (new AddImagesToEntity($request->description_media, $project_description, ["width" => 1024]))->execute();
                $response =[
                    'message' => 'description attached to project successfully',
                    'project_description' => Project::find($request->project_id)->description
                ] ;
                return  $this->returnProjectResponse($response , 200 );
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
        if (!$this->isAllowedToAddProject()) {
            return $this->returnProjectResponse(['message'=>'not alllow to add project']  , 200 );
        }
        $brandsData = $request->brandsData;
        ProjectSupplier::insert($brandsData);
        return $this->returnProjectResponse(['message'=>$brandsData]  , 200 );
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
        if (!$this->isAllowedToAddProject()) {
            return $this->returnProjectResponse(['message'=>'not alllow to add project']  , 200 );
        }
        $designer_data = $request->designersData;
        ProjectDesigner::insert($designer_data);
        return $this->returnProjectResponse(['message'=>$designer_data]  , 200 );
    }

    public function addProjectRole(Request $request)
    {
        if (!$this->isAllowedToAddProject()) {
            return $this->returnProjectResponse(['message'=>'not alllow to add project']  , 200 );
        }       
        $designers = null;
        if ($request->has('designers')) {
            $designers = $request->designers;
            ProjectDesigner::insert($designers);

        }
        $suppliers = null;
        if ($request->has('suppliers')) {
            $suppliers = $request->suppliers;
            ProjectSupplier::insert($suppliers);

        }
        return $this->returnProjectResponse(['message'=>array('designers'=>$designers,'brands'=>$suppliers)]  , 200 );

    }

    // 4- add designer cover
    public function addProjectCover(Request $request)
    {
        if (!$this->isAllowedToAddProject()) {
            return $this->returnProjectResponse(['message'=>'not alllow to add project']  , 200 );
        }       
        $project = Project::find($request->project_id);
        $project->cover_name = $request->cover_name;
        $project->save();
        // if ($request->hasFile('cover')) (new AddImagesToEntity($request->cover,$project, ["width" => 1024]))->execute();
        return response()->json([
            'message' => 'project cover saved ',
            'project' => Project::findOrFail($request->project_id)->images
        ], 200);
    }

    // 5- Add Project Content Image
    public function addProjectContentImage(Request $request)
    {
        $percent = 0.5;
        $project_description = ProjectDescription::firstOrCreate(['project_id' => $request->project_id]);
        $fileName   = $this->generateRandomString(14) . '.' .$request->file('content_media')->getClientOriginalExtension();
        $storagePath =  $request->file('content_media')->storeAs('project' , $fileName , 'public');
        $storageName = basename($storagePath);
        $path = URL::to('/').Storage::url($storagePath);
        if ($path) {
            $image = new ImageEntity();
            $image->img_url = $path;
            $image->thumb_url = $path;
            if ($project_description->images()->save($image)) {
                // list($width, $height) = getimagesize($path);
                // $newwidth = $width * $percent;
                // $newheight = $height * $percent;

                // // Load
                // $thumb = imagecreatetruecolor($newwidth, $newheight);
                // $source = imagecreatefromjpeg($path);

                // // Resize
                // imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                // // Output
                // imagejpeg($thumb);
                // $img = ImageUploader::make($path)->resize(300, 200);
            return $this->returnProjectResponse(array(
                $project_description,
                $path,
                $image,
                ) , 200 );
            }


        }
        // $project_description =  $request->has('content_id')?ProjectDescription::find(['id' => $request->id]):ProjectDescription::create(['project_id' => $request->project_id]);

        // $project = Project::findOrFail($request->project_id);
        // $project_description = ProjectDescription::findOrFail(1);    
        //    $project_description->project_id = $project->id;
        //    if ($request->hasFile('content_media')) {
        //     // $imageName = time().'.'.$request->content_media->extension();  
        //     // $request->content_media->move(public_path('images'), $imageName);
        //     $image      = $request->file('content_media');
        //     $fileName   = time() . '.' . $image->getClientOriginalExtension();
        //     $path = $request->file('content_media')->storeAs('content_media',  $fileName ,'public');
        //     $url = Storage::Url('content_media/'. $fileName);
        //     $image = new Image;
        //     $image->img_url = 'content_media/'.$fileName;
        //     $image->thumb_url = 'content_media/'.$fileName;
        //     $image->imageable_id = '2';
        //     $image->imageable_type = $project_description;
        //     $image->save();
        //     $imageUploader = new AddImagesToEntity2(new ProjectDescription());
        //     if ($request->hasFile('content_media')) {
        //     $imageUploader->UploadAndSave(new ProjectDescription() ,  $request->file('content_media') );
        //     };

        // if ($request->hasFile('content_media')) (new AddImagesToEntity($request->file('content_media'), $project_description, ["width" => 1024]))->execute() ;


            /* Store $imageName name in DATABASE from HERE */
            // $response =[
            //     'project_description' =>$request->hasFile('content_media')
            // ] ;
            // return  $this->returnProjectResponse($response , 200 );
        // }

    }
    
    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
