<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
// use App\Models\Image as ImageEntity;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\URL;
// use Intervention\Image\Facades\Image as ImageUploader;

class ProjectController extends Controller
{

    public function addProject(Request $request, $ownerable, $id)
    {

        $this->validate(
            $request,
            [
                'name' => 'required|string|max:250',
                'title' => 'required|string|max:250',
                'name' => 'required|string|max:250',
                'content' => 'required|string',
                'kind' => 'required|string|max:250',
                'category' => 'required|string|max:2000',
                'country' => 'required|string|max:250',
                'city' => 'required|string|max:250',
                'cover' => 'nullable|mimes:png,jpg|between:1,20000',
                'year' => 'required|string|max:250',
                'types' => 'required|array',
                'types.*' => 'string|max:250',
            ]
        );
        $creator = null;
        if ($ownerable === 'store') {
            $creator = Store::find($id);
        }
        if ($ownerable === 'designer') {
            $creator = User::find($id);
        }
        if ($creator) {
            $project = $creator->projects()->create();
            $project->name      = $request->name;
            $project->category  = $request->category;
            $project->types     = $request->types;
            $project->country   = $request->country;
            $project->city      = $request->city;
            $project->year      = $request->year;
            $project->kind      = $request->kind;
            $project->content      = $request->content;
            $project->title      = $request->title;
            $project->cover = $request->cover->storeOnCloudinary()->getSecurePath();
            if ($project->save()) {
                return response()->json([
                    'status' => true,
                    'message' => "Project created successfully!",
                    'project' => $project
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Error Occurs!",
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Creartore Not Found!",
            ], 404);
        }
    }
}
