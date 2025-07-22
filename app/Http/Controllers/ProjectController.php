<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'order_by' => 'in:name,start_date,end_date|nullable',
            'search' => 'string|nullable',
        ]);

        if ($request->has('search')) {
            $projects = Project::where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('customer', 'LIKE', "%{$request->search}%")
                ->get()
                ->filter((function ($project) {
                    return $project->end_date >= now()->toDateString();
                }))
                ->map(function ($project) {
                    if ($project->image) {
                        $project->image = env('APP_URL', 'http://localhost') . ':' . env('APP_PORT', '8000') . '/storage/images/' . $project->image;
                    }
                    return $project;
                })->values();

            return response()->json($projects);
        }

        $orderBy = $request->input('order_by');

        if ($orderBy == 'start_date') {
            $projects = Project::orderByDesc($orderBy)
                ->get()
                ->filter((function ($project) {
                    return $project->end_date >= now()->toDateString();
                }))
                ->map(function ($project) {
                    if ($project->image) {
                        $project->image = env('APP_URL', 'http://localhost') . ':' . env('APP_PORT', '8000') . '/storage/images/' . $project->image;
                    }
                    return $project;
                })->values();
        }

        $projects = Project::orderBy($orderBy)
            ->get()
            ->filter((function ($project) {
                return $project->end_date >= now()->toDateString();
            }))
            ->map(function ($project) {
                if ($project->image) {
                    $project->image = env('APP_URL', 'http://localhost') . ':' . env('APP_PORT', '8000') . '/storage/images/' . $project->image;
                }
                return $project;
            })->values();

        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'customer' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'image' => 'nullable|image|max:2048',
        ]);

        $project = new Project([
            'name' => $request->name,
            'customer' => $request->customer,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $image->hashName();
            Storage::putFileAs('images', $image, $filename);
            $project->image = $filename;
        }

        $project->save();

        return response()->json($project, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Project $project, Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'customer' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'image' => is_string($request->image) ? 'nullable|string' : 'nullable|image|max:2048',
        ]);

        $project->name = $request->name;
        $project->customer = $request->customer;
        $project->start_date = $request->start_date;
        $project->end_date = $request->end_date;

        if (!$request->hasFile('image')) {
            $project->image = $request->image === 'null' ? null : $project->image;

            $project->save();

            return response()->json($project);
        }

        if ($project->image) {
            Storage::delete('images' . $project->image);
        }

        $image = $request->file('image');
        $filename = $image->hashName();
        Storage::putFileAs('images', $image, $filename);
        $project->image = $filename;

        $project->save();

        return response()->json($project);
    }

    /**
     * Display the specified resource.
     *
     * @param  Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        $project->image_url = $project->image
            ? env('APP_URL', 'http://localhost') . ':' . env('APP_PORT', '8000') . '/storage/images/' . $project->image
            : null;

        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Project $project)
    {
        if ($project->image) {
            Storage::delete('public/images/' . $project->image);
        }

        $project->delete();

        return response()->json(null, 204);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function patch(Request $request, Project $project)
    {
        $request->validate([
            'is_favorite' => 'boolean',
        ]);

        $project->is_favorite = $request->is_favorite ?? false;

        $project->save();

        return response()->json($project);
    }
}

