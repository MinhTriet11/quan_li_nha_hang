<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Branch;
use App\Models\TableTypes;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $query = Table::with(['branch', 'TableTypes']);

        // Filter by search
        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('table_name', 'like', "%{$search}%");
            });
        }

        // Filter by branch
        if ($request->has('branch') && $request->branch !== null) {
            $query->where('id_br', $request->branch);
        }

        // Filter by table type
        if ($request->has('table_type') && $request->table_type !== null) {
            $query->where('table_type_id', $request->table_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $tables = $query->orderBy('id_br')->orderBy('table_name')->paginate(10);
        $branches = Branch::all();
        $tableTypes = TableTypes::all();

        return view('admin.pages.tables', compact('tables', 'branches', 'tableTypes'));
    }

    public function create()
    {
        $branches = Branch::all();
        $tableTypes = TableTypes::all();
        return view('admin.pages.table_create', compact('branches', 'tableTypes'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'table_name' => 'required|string|max:255',
                'id_br' => 'required|exists:branches,id_br',
                'table_type_id' => 'required|exists:table_types,id',
                'status' => 'required|in:0,1,2',
            ], [
                'table_name.required' => 'Tên bàn là bắt buộc',
                'id_br.required' => 'Vui lòng chọn chi nhánh',
                'id_br.exists' => 'Chi nhánh không tồn tại',
                'table_type_id.required' => 'Vui lòng chọn loại bàn',
                'table_type_id.exists' => 'Loại bàn không tồn tại',
                'status.required' => 'Vui lòng chọn trạng thái',
            ]);

            $tableName = Table::where('table_name', $request->table_name)
                        ->where('id_br', $request->id_br)
                        ->first();

            if(!empty($tableName)){
                return response()->json([
                    'error' => false,
                    'message' => 'Tên bàn đã tồn tại trong chi nhánh này',
                ], 422);
            }

            $table = Table::create([
                'table_name' => $request->table_name,
                'id_br' => $request->id_br,
                'table_type_id' => $request->table_type_id,
                'status' => $request->status,
            ]);

            if ($table) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo bàn thành công!',
                    'redirect' => route('admin.table.create')
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Vui lòng thử lại sau',
                ]);
            }
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
        $table = Table::with(['branch', 'TableTypes'])->findOrFail($id);
        $branches = Branch::all();
        $tableTypes = TableTypes::all();
        return view('admin.pages.table_show', compact('table', 'branches', 'tableTypes'));
    }

    public function edit($id)
    {
        $table = Table::with(['branch', 'TableTypes'])->findOrFail($id);
        $branches = Branch::all();
        $tableTypes = TableTypes::all();
        return view('admin.pages.table_edit', compact('table', 'branches', 'tableTypes'));
    }

    public function update(Request $request, $id)
    {
        try{
        $request->validate([
            'table_name' => 'required|string|max:255',
            'id_br' => 'required|exists:branches,id_br',
            'table_type_id' => 'required|exists:table_types,id',
            'status' => 'required|in:0,1,2',
        ], [
            'table_name.required' => 'Tên bàn là bắt buộc',
            'id_br.required' => 'Vui lòng chọn chi nhánh',
            'id_br.exists' => 'Chi nhánh không tồn tại',
            'table_type_id.required' => 'Vui lòng chọn loại bàn',
            'table_type_id.exists' => 'Loại bàn không tồn tại',
            'status.required' => 'Vui lòng chọn trạng thái',
        ]);

        // Lấy bàn hiện tại
        $table = Table::findOrFail($id);

        // Chỉ kiểm tra trùng nếu tên bàn thay đổi
        if ($table->table_name !== $request->table_name || $table->id_br != $request->id_br) {
            $tableName = Table::where('table_name', $request->table_name)
                            ->where('id_br', $request->id_br)
                            ->where('id_table', '!=', $id) // Loại trừ bản ghi hiện tại
                            ->first();

            if(!empty($tableName)){
                return response()->json([
                    'error' => false,
                    'message' => 'Tên bàn đã tồn tại trong chi nhánh này',
                ], 422);
            }
        }

        $table->update([
            'table_name' => $request->table_name,
            'id_br' => $request->id_br,
            'table_type_id' => $request->table_type_id,
            'status' => $request->status,
        ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật bàn thành công',
            ]);
        }catch(ValidationException $e){
            return response()->json([
                'error' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $table = Table::findOrFail($id);

            if($table->status == 1){
                $table->delete();
                return redirect()->route('admin.table')->with('success', 'Xóa bàn thành công');
            } else {
                return redirect()->route('admin.table')->with('error', 'Chỉ có thể xóa bàn đang bảo trì');
            }

        } catch (\Exception $e) {
            return redirect()->route('admin.table')->with('error', 'Có lỗi xảy ra hoặc tồn tại lịch đặt trong bàn này');
        }
    }

    // Table Types Management
    public function tableTypeIndex()
    {
        $tableTypes = TableTypes::withCount('tables')->orderBy('name')->paginate(4);
        return view('admin.pages.table_types', compact('tableTypes'));
    }

    public function tableTypeCreate()
    {
        return view('admin.pages.table_type_create');
    }

    public function tableTypeStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:table_types,name',
            ], [
                'name.required' => 'Tên loại bàn là bắt buộc',
                'name.string' => 'Tên loại bàn phải là chuỗi',
                'name.max' => 'Tên loại bàn không được vượt quá 255 ký tự',
                'name.unique' => 'Tên loại bàn đã tồn tại',
            ]);

            TableTypes::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm loại bàn thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function tableTypeEdit($id)
    {
        $tableType = TableTypes::findOrFail($id);
        return view('admin.pages.table_type_edit', compact('tableType'));
    }

    public function tableTypeUpdate(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:table_types,name,' . $id,
            ], [
                'name.required' => 'Tên loại bàn là bắt buộc',
                'name.string' => 'Tên loại bàn phải là chuỗi',
                'name.max' => 'Tên loại bàn không được vượt quá 255 ký tự',
                'name.unique' => 'Tên loại bàn đã tồn tại',
            ]);

            $tableType = TableTypes::findOrFail($id);
            $tableType->update([
                'name' => $request->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật loại bàn thành công!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function tableTypeDestroy($id)
    {
        try {
            $tableType = TableTypes::findOrFail($id);

            // Check if there are any tables using this type
            $tableCount = Table::where('table_type_id', $id)->count();

            if ($tableCount > 0) {
                return redirect()->route('admin.tableType')->with('error', 'Không thể xóa loại bàn này vì đang có ' . $tableCount . ' bàn sử dụng loại bàn này');
            }

            $tableType->delete();

            return redirect()->route('admin.tableType')->with('success', 'Xóa loại bàn thành công');
        } catch (\Exception $e) {
            return redirect()->route('admin.tableType')->with('error', 'Có lỗi xảy ra');
        }
    }
}
