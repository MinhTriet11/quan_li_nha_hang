<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('menuItems')->orderBy('name')->paginate(10);
        return view('admin.pages.categories', compact('categories'));
    }

    public function create()
    {
        return view('admin.pages.category_create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'name.required' => 'Tên danh mục là bắt buộc',
                'name.string' => 'Tên danh mục phải là chuỗi',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',
                'image.image' => 'File phải là hình ảnh',
                'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg',
                'image.max' => 'Hình ảnh không được vượt quá 2MB',
            ]);

            $imageName = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/categories', $imageName);
            }

            Category::create([
                'name' => $request->name,
                'image' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm danh mục món ăn thành công!',
            ]);

            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.pages.category_edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id_cate',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'name.required' => 'Tên danh mục là bắt buộc',
                'name.string' => 'Tên danh mục phải là chuỗi',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',
                'image.image' => 'File phải là hình ảnh',
                'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg',
                'image.max' => 'Hình ảnh không được vượt quá 2MB',
            ]);

            $category = Category::findOrFail($id);

            $imageName = $category->image;

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($category->image && file_exists(storage_path('app/public/categories/' . $category->image))) {
                    unlink(storage_path('app/public/categories/' . $category->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/categories', $imageName);
            }

            $category->update([
                'name' => $request->name,
                'image' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục món ăn thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if there are any menu items using this category
            $menuItemCount = MenuItem::where('id_cate', $id)->count();

            if ($menuItemCount > 0) {
                return redirect()->route('admin.category')->with('error', 'Không thể xóa danh mục này vì đang có ' . $menuItemCount . ' món ăn thuộc danh mục này');
            }

            $category->delete();

            return redirect()->route('admin.category')->with('success', 'Xóa danh mục món ăn thành công');
        } catch (\Exception $e) {
            return redirect()->route('admin.category')->with('error', 'Có lỗi xảy ra');
        }
    }
}
