<?php

namespace App\Http\Controllers;
use App\Models\GuidanceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminGuidanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = GuidanceModel::all();
        return view('admin.manage_guidance',["data" => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'nullable|string',
            'link_path' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['title', 'content', 'link_path']);

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('guidance_images', 'public');
                $data['image_path'] = $imagePath;
            }

            $guidance = GuidanceModel::create($data);

            return response()->json([
                'message' => 'Guidance created successfully',
                'data' => $guidance
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create guidance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $guidance = GuidanceModel::findOrFail($id);
            return response()->json($guidance);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Guidance not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GuidanceModel $guidanceModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'content' => 'nullable|string',
            'link_path' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $guidance = GuidanceModel::findOrFail($id);
            $data = $request->only(['title', 'content', 'link_path']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($guidance->image_path) {
                    Storage::disk('public')->delete($guidance->image_path);
                }

                $image = $request->file('image');
                $imagePath = $image->store('guidance_images', 'public');
                $data['image_path'] = $imagePath;
            }

            $guidance->update($data);

            return response()->json([
                'message' => 'Guidance updated successfully',
                'data' => $guidance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update guidance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $guidance = GuidanceModel::findOrFail($id);

            // Delete image if exists
            if ($guidance->image_path) {
                Storage::disk('public')->delete($guidance->image_path);
            }

            $guidance->delete();

            return response()->json([
                'message' => 'Guidance deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete guidance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

