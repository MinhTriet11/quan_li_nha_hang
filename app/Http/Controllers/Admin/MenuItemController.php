<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category');

        // Filter by search
        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            // $query->where(function ($q) use ($search) {
            //     $q->where('name', 'like', "%{$search}%")       
            //       ->orWhere('description', 'like', "%{$search}%");
            // });   // Dùng để nhóm các điều kiện OR lại thành một nhóm (tạo dấu ngoặc trong SQL)

            $query->where('name','like', "%{$search}%");
        }

        // Filter by category
        if ($request->has('category') && $request->category !== null) {
            $query->where('id_cate', $request->category);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $menuItems = $query->orderBy('id_food', 'desc')->paginate(10);
        $categories = Category::all();

        return view('admin.pages.menu_items', compact('menuItems', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.pages.menu_item_create', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:menu_items,name',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'id_cate' => 'required|exists:categories,id_cate',
                'status' => 'required|in:0,1',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
            ], [
                'name.required' => 'Tên món ăn là bắt buộc',
                'name.unique' => 'Tên món ăn đã tồn tại',
                'description.required' => 'Mô tả là bắt buộc',
                'price.required' => 'Giá là bắt buộc',
                'price.numeric' => 'Giá phải là số',
                'price.min' => 'Giá phải lớn hơn 0',
                'id_cate.required' => 'Vui lòng chọn danh mục',
                'id_cate.exists' => 'Danh mục không tồn tại',
                'status.required' => 'Vui lòng chọn trạng thái',
                'image.required' => 'File phải là bắt buộc',
                'image.image' => 'File phải là hình ảnh',
                'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg',
                'image.max' => 'Hình ảnh không được vượt quá 3MB',
            ]);

            $imageName = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/menu_items', $imageName);
            }

            MenuItem::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'id_cate' => $request->id_cate,
                'status' => $request->status,
                'image' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm món ăn thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function show($id)
    {
        $menuItem = MenuItem::with('category')->findOrFail($id);
        $recipes = $menuItem->recipe()
            ->with('ingredient')
            ->paginate(5)
            ->withQueryString();
        $ingredients = Ingredient::where('status', 1)->orderBy('name')->get();

        return view('admin.pages.menu_item_show', compact('menuItem', 'ingredients', 'recipes'));
    }

    public function edit($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $categories = Category::all();
        
        return view('admin.pages.menu_item_edit', compact('menuItem', 'categories'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:menu_items,name,' . $id . ',id_food',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'id_cate' => 'required|exists:categories,id_cate',
                'status' => 'required|in:0,1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
            ], [
                'name.required' => 'Tên món ăn là bắt buộc',
                'name.unique' => 'Tên món ăn đã tồn tại',
                'price.required' => 'Giá là bắt buộc',
                'price.numeric' => 'Giá phải là số',
                'price.min' => 'Giá phải lớn hơn 0',
                'id_cate.required' => 'Vui lòng chọn danh mục',
                'id_cate.exists' => 'Danh mục không tồn tại',
                'status.required' => 'Vui lòng chọn trạng thái',
                'image.image' => 'File phải là hình ảnh',
                'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif hoặc svg',
                'image.max' => 'Hình ảnh không được vượt quá 3MB',
            ]);

            $menuItem = MenuItem::findOrFail($id);

            $imageName = $menuItem->image;

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($menuItem->image && file_exists(storage_path('app/public/menu_items/' . $menuItem->image))) {
                    unlink(storage_path('app/public/menu_items/' . $menuItem->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/menu_items', $imageName);
            }

            $menuItem->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'id_cate' => $request->id_cate,
                'status' => $request->status,
                'image' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật món ăn thành công!',
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
            $menuItem = MenuItem::findOrFail($id);
            if($menuItem->status == 1){
                $menuItem->delete();
                return redirect()->route('admin.menuItem')->with('success', 'Xóa món ăn thành công');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.menuItem')->with('error', 'Có lỗi xảy ra');
        }
    }
}
