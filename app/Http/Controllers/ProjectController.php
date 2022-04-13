<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Store;
use App\Models\User;
use App\Models\ProductIdentity;
use Illuminate\Http\Request;
use Throwable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{

    public function addProject(Request $request, $ownerable, $id)
    {

        $this->validate(
            $request,
            [
                'name' => 'required|string|max:250',
                'title' => 'required|string|max:250',
                'content' => 'required|string',
                'kind' => 'nullable|array',
                'kind.*' => 'nullable|string|max:250',
                'article_type' => 'required|string|max:2000',
                'country' => 'required|string|max:250',
                'city' => 'required|string|max:250',
                'cover' => 'nullable|mimes:png,jpg|between:1,20000',
                'year' => 'required|string|max:250',
                'type' => 'nullable|array',
                'type.*' => 'required|string|max:250',
                'stores' => 'nullable|array',
                'images' => 'nullable|array',
                'images.*' => 'nullable|string|max:250',
                'stores.*' => 'numeric|max:250',
                'users' => 'nullable|array',
                'users.*' => 'numeric|max:250',
                'products' => 'nullable|array',
                'products.*' => 'numeric|max:250',
            ]
        );
        $creator = null;
        if ($ownerable === 'store') {
            $creator = Store::find($id);
        }
        if ($ownerable === 'designer') {
            $creator = User::find($id);
        }
        $brands = $request->stores;
        $users = $request->users;
        $products = $request->products;
        if ($creator) {
            $project = $creator->projects()->create();
            $project->name      = $request->name;
            $project->article_type  = $request->article_type;
            $project->type     = $request->type;
            $project->country   = $request->country;
            $project->city      = $request->city;
            $project->year      = $request->year;
            $project->images      = $request->images;
            $project->kind      = $request->kind;
            $project->content      = $request->content;
            $project->title      = $request->title;
            $project->cover = $request->cover->storeOnCloudinary()->getSecurePath();
            if ($project->save()) {
                try {
                    $project->brandRoles()->syncWithoutDetaching($brands);
                    $project->designerRoles()->syncWithoutDetaching($users);
                    $project->productsTagged()->syncWithoutDetaching($products);

                    return response()->json([
                        'status' => true,
                        'message' => "Project created successfully!",
                        'project' => $project,
                        'stores' => $request->stores
                    ], 201);
                } catch (Throwable $err) {
                    return response()->json([
                        'status' => false,
                        'message' => "Error",
                    ], 200);
                }
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

    public function editProject(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|string|max:250',
                'content' => 'required|string',
                'kind' => 'required|string|max:250',
                'article_type' => 'required|string|max:2000',
                'country' => 'required|string|max:250',
                'city' => 'required|string|max:250',
                'cover' => 'nullable|mimes:png,jpg|between:1,20000',
                'year' => 'required|string|max:250',
                'type' => 'required|string|max:250',
                'stores' => 'nullable|array',
                'stores.*' => 'numeric|max:250',
                'users' => 'nullable|array',
                'users.*' => 'numeric|max:250',
                'products' => 'nullable|array',
                'products.*' => 'numeric|max:250',
            ]
        );
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => false,
                'message' => "Project Not found or Deleted"
            ], 200);
        }
        $brands = $request->stores;
        $users = $request->users;
        $products = $request->products;
        $project->name      = $request->name;
        $project->article_type  = $request->article_type;
        $project->type     = $request->type;
        $project->country   = $request->country;
        $project->city      = $request->city;
        $project->year      = $request->year;
        $project->kind      = $request->kind;
        $project->content      = $request->content;
        $project->images      = $request->images;
        $project->cover = $request->cover->storeOnCloudinary()->getSecurePath();
        if ($project->save()) {
            try {
                $project->brandRoles()->syncWithoutDetaching($brands);
                $project->designerRoles()->syncWithoutDetaching($users);
                $project->productsTagged()->syncWithoutDetaching($products);

                return response()->json([
                    'status' => true,
                    'message' => "Project EDITED successfully!",
                    'project' => $project,
                    'stores' => $request->stores
                ], 201);
            } catch (Throwable $err) {
                return response()->json([
                    'status' => false,
                    'message' => "Error",
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Error Occurs!",
            ], 500);
        }
    }

    public function getProjectById($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json([
                'message' => "NOT FOUND"
            ], 200);
        } else {
            $brands = $project->brandRoles()->get();
            $designers = $project->designerRoles()->get();
            $products_tags = $project->productsTagged()->latest()->take(4)->get();
            $similars = Project::latest()->where('kind', $project->kind)
                ->where('type', $project->type)
                ->take(3)->get();
            return response()->json([
                'project' => $project,
                'brands' => $brands,
                'designers' => $designers,
                'products_tags' => $products_tags,
                'similar' => $similars
            ], 200);
        }
    }

    public function moreSimilar($type, $kind)
    {
        $moreSimilars = Project::latest()->where('kind', $kind)
            ->where('type', $type)
            ->paginate(12);
        return response()->json([
            'projects' => $moreSimilars
        ], 200);
    }

    public function moreTaggedProducts($id)

    {
        $project = Project::find($id);
        $products_tags = $project->productsTagged()->latest()->paginate(12);
        return response()->json([
            'projects' => $products_tags
        ], 200);
    }

    public function roleStepData()
    {
        $designers = User::all()->where('is_designer', 1);
        $brands = Store::all();

        return response()->json([
            'status' => true,
            'users' =>  $designers,
            'brands' =>  $brands,
        ], 200);
    }

    public function getTagStepProducts()
    {
        $products = QueryBuilder::for(ProductIdentity::class)
            ->allowedFilters([
                AllowedFilter::exact('category'),
                AllowedFilter::exact('kind'),
                AllowedFilter::exact('store_id')
            ])
            ->get();
        if (!empty($products)) {
            return response()->json([
                'products' => $products,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Products Added! ',
            ], 200);
        }
    }

    public function magazineFilter($offset)
    {
        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters([
                AllowedFilter::exact('ownerable_type'),
                AllowedFilter::exact('country'),
                AllowedFilter::exact('year'),
                'kind'
            ])
            ->offset($offset)
            ->take(15)
            ->get(['id', 'article_type', 'name', 'cover', 'country', 'city', 'year', 'kind', 'type', "ownerable_type"]);
        if (!empty($projects)) {
            return response()->json([
                'projects' => $projects,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Projects Added! ',
            ], 200);
        }
    }
}
