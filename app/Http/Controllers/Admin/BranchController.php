<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        $branches = $query->orderBy('name', 'asc')->paginate(10);

        return view('admin.pages.branches', compact('branches'));
    }

    public function create()
    {
        return view('admin.pages.branch_create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'    => 'required|string|max:255|unique:branches,name',
                'address' => 'required|string|max:255',
                'location'=> 'required|string|max:255',
                'status'  => 'required|in:0,1',
                'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
            ], [
                'name.required'    => 'Tên chi nhánh là bắt buộc',
                'name.unique'      => 'Tên chi nhánh đã tồn tại',
                'address.required' => 'Địa chỉ là bắt buộc',
                'location.required'=> 'Vị trí (Google Maps link) là bắt buộc',
                'status.required'  => 'Vui lòng chọn trạng thái',
                'image.image'      => 'File phải là hình ảnh',
                'image.mimes'      => 'Ảnh phải có định dạng jpg, jpeg hoặc png',
                'image.max'        => 'Ảnh không được vượt quá 3MB',
            ]);

            $data = [
                'name'     => $request->name,
                'address'  => $request->address,
                'location' => $request->location,
                'status'   => $request->status,
            ];

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'));
            }

            $branch = Branch::create($data);

            if ($branch) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Tạo chi nhánh thành công!',
                    'redirect' => route('admin.branch.create'),
                ]);
            }

            return response()->json([
                'error'   => false,
                'message' => 'Vui lòng thử lại sau',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        return view('admin.pages.branch_show', compact('branch'));
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('admin.pages.branch_edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            $request->validate([
                'name'    => 'required|string|max:255|unique:branches,name,' . $id . ',id_br',
                'address' => 'required|string|max:255',
                'location'=> 'required|string|max:255',
                'status'  => 'required|in:0,1',
                'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
            ], [
                'name.required'    => 'Tên chi nhánh là bắt buộc',
                'name.unique'      => 'Tên chi nhánh đã tồn tại',
                'address.required' => 'Địa chỉ là bắt buộc',
                'location.required'=> 'Vị trí là bắt buộc',
                'status.required'  => 'Vui lòng chọn trạng thái',
                'image.image'      => 'File phải là hình ảnh',
                'image.mimes'      => 'Ảnh phải có định dạng jpg, jpeg hoặc png',
                'image.max'        => 'Ảnh không được vượt quá 3MB',
            ]);

            $branch->name     = $request->name;
            $branch->address  = $request->address;
            $branch->location = $request->location;
            $branch->status   = $request->status;

            if ($request->hasFile('image')) {
                $this->deleteImage($branch->image);
                $branch->image = $this->uploadImage($request->file('image'));
            }

            $branch->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chi nhánh thành công',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $branch = Branch::findOrFail($id);

            if ($branch->status == 0) {
                $this->deleteImage($branch->image);
                $branch->delete();
                return redirect()->route('admin.branch')->with('success', 'Xóa chi nhánh thành công');
            }

            return redirect()->route('admin.branch')->with('error', 'Chỉ có thể xóa chi nhánh ngưng hoạt động');
        } catch (\Exception $e) {
            return redirect()->route('admin.branch')->with('error', 'Có lỗi xảy ra hoặc còn dữ liệu liên quan');
        }
    }

    private function uploadImage($file): string
    {
        $dir = public_path('uploads/branches');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $name = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $file->move($dir, $name);
        return 'uploads/branches/' . $name;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) return;
        $full = public_path($path);
        if (File::exists($full)) {
            File::delete($full);
        }
    }
}