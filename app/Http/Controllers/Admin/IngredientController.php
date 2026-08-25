<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $ingredients = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.pages.ingredient', compact('ingredients'));
    }

    public function create(){
        $ingredients = Ingredient::all();

        return view('admin.pages.ingredient_create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'   => 'required|string|max:255|unique:ingredients,name',
                'unit'   => 'required|string|max:50',
                'min'    => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
            ], [
                'name.required'   => 'Tên nguyên liệu là bắt buộc',
                'name.unique'     => 'Tên nguyên liệu đã tồn tại',
                'unit.required'   => 'Đơn vị là bắt buộc',
                'min.required'    => 'Mức cảnh báo là bắt buộc',
                'min.numeric'     => 'Mức cảnh báo phải là số',
                'min.min'         => 'Mức cảnh báo phải lớn hơn hoặc bằng 0',
                'status.required' => 'Vui lòng chọn trạng thái',
                'status.in'       => 'Trạng thái không hợp lệ',
            ]);

            Ingredient::create([
                'name'      => $request->name,
                'unit'      => $request->unit,
                'min_stock' => $request->min,
                'status'    => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm nguyên liệu thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function edit($idIngre){
        $ingreDetail = Ingredient::findOrFail($idIngre);
        return view('admin.pages.ingredient_edit', compact('ingreDetail'));
    }

    public function update(Request $request, $id){
        try{
            $request->validate([
                'name'   => 'required|string|max:255|unique:ingredients,name,' . $id . ',id_ingre',
                'unit'   => 'required|string|max:50',
                'min'    => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
            ], [
                'name.required'   => 'Tên nguyên liệu là bắt buộc',
                'name.unique'     => 'Tên nguyên liệu đã tồn tại',
                'unit.required'   => 'Đơn vị là bắt buộc',
                'min.required'    => 'Mức cảnh báo là bắt buộc',
                'min.numeric'     => 'Mức cảnh báo phải là số',
                'min.min'         => 'Mức cảnh báo phải lớn hơn hoặc bằng 0',
                'status.required' => 'Vui lòng chọn trạng thái',
                'status.in'       => 'Trạng thái không hợp lệ',
            ]);

            $ingreDetail = Ingredient::findOrFail($id);

            $ingreDetail->update([
                'name' => $request->name,
                'unit' => $request->unit,
                'min_stock' => $request->min,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => $ingreDetail,
                'message' => 'Cập nhật thành công'
            ]);

        }catch(ValidationException $e){
            return response()->json([
                'error' => true,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function getUnit($id){
        $ingreDetail = Ingredient::findOrFail($id);

        $unit = $ingreDetail->unit;

        return response()->json([
            'data' => $unit
        ]);
    }

    public function destroy($id){
        try {
            $ingreItem = Ingredient::findOrFail($id);

            // Neu nguyen lieu dang duoc dung trong cong thuc -> khong cho xoa
            if ($ingreItem->recipe()->exists()) {
                return redirect()->back()->with('error', 'Nguyên liệu đang được sử dụng trong công thức, không thể xóa');
            }

            $ingreItem->delete();
            return redirect()->back()->with('success', 'Xóa nguyên liệu thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }
}
