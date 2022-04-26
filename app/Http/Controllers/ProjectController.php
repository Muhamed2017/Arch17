<?php

namespace App\Http\Controllers;

use App\Models\Board;
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
                'type' => 'nullable|string',
                // 'type.*' => 'required|string|max:250',
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
                // 'kind' => 'required|string|max:250',

                'kind' => 'nullable|array',
                'kind.*' => 'nullable|string|max:250',
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
            $owner = $project->ownerable()->get();
            $products_tags = $project->productsTagged()->latest()->take(4)->get();
            $similars = Project::latest()->where('type', $project->type)
                ->take(3)->get();
            return response()->json([
                'project' => $project,
                'brands' => $brands,
                'designers' => $designers,
                'products_tags' => $products_tags,
                'similar' => $similars,
                'owner' => $owner

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
                // AllowedFilter::exact('ownerable_type'),
                AllowedFilter::exact('country'),
                AllowedFilter::exact('article_type'),
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

    // delete project
    public function deleteProject($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project Not fount or already deleted '
            ], 404);
        }
        try {
            $project->delete();
            return response()->json([
                'success' => true,
                'message' => "Project deleted Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'error' => $err
            ], 500);
        }
    }

    // collect project apis


    // create fresh collection / board api
    public function makeNewProjectCollection(Request $request)
    {
        $this->validate($request, [
            'user_id'          => 'required|string',
            'name'          => 'required|string',
        ]);

        $board = new Board();
        $board->user_id = $request->user_id;
        $board->name = $request->name;

        if ($board->save()) {
            return response()->json([
                'success' => true,
                'board' => $board,
                'message' => "Project Collection Created Successfully"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs, Try Again"
            ], 500);
        }
    }

    public function saveToBoard(Request $request)
    {
        $this->validate($request, [
            'project_id'          => 'required|string',
            'board_id'          => 'required|string',
        ]);

        $project = Project::find($request->project_id);
        $board = Board::find($request->board_id);
        try {
            $project->boards()->attach($board);
            return response()->json([
                'success' => true,
                'message' => "Saved to collection Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs",
                'error' => $err
            ], 500);
        }
    }

    public function removerFromBoard(Request $request)
    {
        $this->validate($request, [
            'project_id'          => 'required|string',
            'board_id'          => 'required|string',
        ]);

        $project = Project::find($request->project_id);
        $board = Board::find($request->board_id);
        try {
            $project->boards()->detach($board);
            return response()->json([
                'success' => true,
                'message' => "Project Removed from collection Successfully"
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'success' => false,
                'message' => "Error Occurs",
                'error' => $err
            ], 500);
        }
    }

    public function allBoards()
    {
        $boards = Board::all();

        if (!empty($boards)) {
            return response()->json([
                'boards' => $boards,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Projects Added! ',
            ], 200);
        }
    }


    public function listAllBoards($id)
    {


        $boards = Board::with('projects')->where('user_id', $id)->get();

        if (!empty($boards)) {
            return response()->json([
                'projects' => $boards,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No projects Added! ',
            ], 200);
        }
    }

    public function listAllBoardsByProject($user_id, $project_id)
    {

        $_saved_in = [];
        $_unsaved = [];
        $boards = Board::all()->where('user_id', $user_id);
        $project = Project::find($project_id);
        foreach ($boards as $board) {
            if ($board->projects->contains($project)) {
                array_push($_saved_in, ['name' => $board->name, 'id' => $board->id, 'saved' => true]);
            } else {
                array_push($_unsaved, ['name' => $board->name, 'id' => $board->id, 'saved' => false]);
            }
        }
        if (!empty($boards)) {
            return response()->json([
                'saved_in' => $_saved_in,
                'unsaved_in' => $_unsaved
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Boards Added! ',
            ], 200);
        }
    }


    public function getUserBoardById($id)
    {

        $board = Board::find($id);
        $projects = $board->projects()->get();
        return response()->json([
            'status' => true,
            'board' =>  $board,
            'projects' => $projects
        ], 200);
    }
    public function editBoard(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:250',
        ]);

        $board = Board::find($id);
        $board->name = $request->name;
        if ($board->save()) {
            return response()->json([
                'status' => true,
                'board' =>  $board,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
            ], 500);
        }
    }

    public function deleteBoard($id)
    {

        $board = Board::find($id);

        if ($board) {
            try {
                $board->delete();
                return response()->json([
                    'success' => true,
                    'message' => "board deleted Successfully"
                ], 200);
            } catch (Throwable $err) {
                return response()->json([
                    'success' => false,
                    'error' => $err
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "board Not Found"
            ], 200);
        }
    }
}
