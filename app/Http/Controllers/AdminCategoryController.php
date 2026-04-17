<?php

namespace App\Http\Controllers;

use enshrined\svgSanitize\Sanitizer;
use App\Models\CategoryModel;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = CategoryModel::orderBy('id', 'desc')->get();
        return response()->json($categories);
    }

    public function manageCategories()
    {
        return view('admin.manage_categories');
    }


    public function show($id)
    {
        $category = CategoryModel::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function store(Request $request)
    {
        try {

            // VALIDASI
            $data = $request->validate(
                [
                    'category_name'        => 'required|string|max:255|unique:tbl_category,category_name',
                    'category_description' => 'required|string|max:255',
                    'category_icon'        => 'nullable|string', // SVG
                ],
                [],
                [],
                function ($validator) {
                    throw new \Illuminate\Http\Exceptions\HttpResponseException(
                        response()->json([
                            'message' => 'Validasi gagal',
                            'errors'  => $validator->errors()
                        ], 422)
                    );
                }
            );

            // ================================
            // 🔒 SANITASI SVG CATEGORY_ICON
            // ================================
            if (!empty($data['category_icon'])) {

                $sanitizer = new Sanitizer();

                // Buang script/atribut berbahaya
                $cleanSvg = $sanitizer->sanitize($data['category_icon']);

                if (!$cleanSvg) {
                    return response()->json([
                        'message' => 'SVG tidak valid atau berbahaya',
                        'error'   => 'invalid_svg'
                    ], 422);
                }

                $data['category_icon'] = $cleanSvg;
            }

            // SIMPAN KE DATABASE
            $category = CategoryModel::create($data);

            return response()->json([
                'message' => 'Category created',
                'data'    => $category
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'server_error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $category = CategoryModel::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        try {
            $data = $request->validate(
                [
                    'category_name'        => 'required|string|max:255|unique:tbl_category,category_name,' . $id,
                    'category_description' => 'required|string|max:255',
                    'category_icon'        => 'nullable|string', // SVG
                ]
            );

            // SANITASI SVG CATEGORY_ICON
            if (!empty($data['category_icon'])) {

                $sanitizer = new Sanitizer();

                // Buang script/atribut berbahaya
                $cleanSvg = $sanitizer->sanitize($data['category_icon']);

                if (!$cleanSvg) {
                    return response()->json([
                        'message' => 'SVG tidak valid atau berbahaya',
                        'error'   => 'invalid_svg'
                    ], 422);
                }

                $data['category_icon'] = $cleanSvg;
            }

            $category->update($data);

            return response()->json([
                'message' => 'Category updated',
                'data'    => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        $category = CategoryModel::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        try {
            $category->delete();
            return response()->json([
                'message' => 'Category deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'server error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
