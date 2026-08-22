<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    public function store(Request $request){
        try {
            $request->validate([
                'id_food' => 'required',
                'id_ingre' => 'required',
                'quantity' => 'required|numeric|min:0',
            ], [
                'id_food.required' => 'Món ăn là bắt buộc',
                'id_ingre.required' => 'Nguyên liệu là bắt buộc',
                'quantity.numeric' => 'Giá phải là số',
                'quantity.min' => 'Giá phải lớn hơn 0'
            ]);

            // Nếu (id_food, id_ingre) đã tồn tại thì cộng quantity vào record cũ
            $recipe = Recipe::where('id_food', $request->id_food)
                ->where('id_ingre', $request->id_ingre)
                ->first();

            if ($recipe) {
                $recipe->quantity = $recipe->quantity + $request->quantity;
                $recipe->save();

                $message = 'Nguyên liệu đã có trong công thức, đã cộng thêm số lượng!';
            } else {
                $recipe = Recipe::create([
                    'id_food' => $request->id_food,
                    'id_ingre' => $request->id_ingre,
                    'quantity' => $request->quantity,
                ]);

                $message = 'Thêm công thức thành công!';
            }

            if($recipe){
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.menuItem.show', $request->id_food)
                ]);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function update(Request $request, $id){

        try {
            $request->validate([
                'quantity' => 'required|numeric|min:0',
            ], [
                'quantity.required' => 'Số lượng là bắt buộc',
                'quantity.numeric' => 'Số lượng phải là số',
                'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 0',
            ]);

            $recipe = Recipe::findOrFail($id);
            $recipe->quantity = $request->quantity;
            $result = $recipe->save();

            if($result){
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật công thức thành công',
                ]);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function destroy($id){
        try {
            $recipeItem = Recipe::findOrFail($id);
            if($recipeItem){
                $recipeItem->delete();
                return redirect()->back()->with('success', 'Xóa nguyên liệu thành công');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }
}
