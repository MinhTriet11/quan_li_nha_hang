<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BranchScope;
use App\Http\Controllers\Controller;
use App\Models\Books;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Table;
use App\Models\TableTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AdminBookController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $query = Books::with(['customer', 'table', 'branch', 'tableType']);

        // Auto-scope theo chi nhánh của user nếu user bị khóa cứng
        $lockedBranch = $this->userBranchId();
        if ($lockedBranch) {
            $query->where('id_br', $lockedBranch);
        }

        // Filter by search
        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_phone', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by date
        if ($request->has('date') && $request->date !== null) {
            $query->whereDate('time', $request->date);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // Filter by branch (chỉ áp dụng khi user không bị khóa cứng)
        if (!$lockedBranch && $request->has('branch') && $request->branch !== null) {
            $query->where('id_br', $request->branch);
        }

        $books = $query->orderBy('time', 'desc')->paginate(10);
        $branches = $this->availableBranches();
        $showBranchSelector = $this->showBranchSelector();

        return view('admin.pages.books', compact('books', 'branches', 'showBranchSelector'));
    }

    public function availableTables(Request $request)
    {
        $branchId = $request->branch;
        $date = $request->date;
        $time = $request->time;
        $tableTypeId = $request->table_type;

        $dateTime = $date . ' ' . $time;
        $bookingTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

        $windowStart = $bookingTime->copy()->subHours(2);
        $windowEnd = $bookingTime->copy()->addHours(2);

        
        $bookedTableIds = Books::where('id_br', $branchId)
            ->whereBetween('time', [$windowStart, $windowEnd])
            ->whereIn('status', [0, 1]) 
            ->pluck('id_table');

        // Nếu có table_type, trả về danh sách loại bàn cụ thể
        if ($tableTypeId) {
            $tables = Table::where('id_br', $branchId)
                ->where('table_type_id', $tableTypeId)
                ->whereNotIn('id_table', $bookedTableIds)
                ->get()
                ->map(function ($item) {
                    return [
                        'table_id' => $item->id_table,
                        'table_name' => $item->table_name,
                    ];
                });

            return response()->json([
                'data' => $tables
            ]);
        }

        // Lấy các bàn của chi nhánh, trừ các bàn đã được đặt (theo loại bàn)
        $availableTables = Table::where('id_br', $branchId)
            ->whereNotIn('id_table', $bookedTableIds)
            ->selectRaw('table_type_id, COUNT(*) as available_count')
            ->groupBy('table_type_id')
            ->get();

        // Lấy thông tin loại bàn và merge vào kết quả
        $tableTypeIds = $availableTables->pluck('table_type_id')->unique();
        $tableTypes = TableTypes::whereIn('id', $tableTypeIds)->pluck('name', 'id');

        $availableTables = $availableTables->map(function ($item) use ($tableTypes) {
            $item->table_type_name = $tableTypes[$item->table_type_id] ?? null;
            return $item;
        });

        return response()->json([
            'data' => $availableTables
        ]);
    }

    public function create()
    {
        $branches = Branch::all();
        $customers = Customer::all();
        $tableTypes = TableTypes::all();
        return view('admin.pages.book_create', compact('branches', 'customers', 'tableTypes'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                'guest_email' => 'required|email',
                'id_br' => 'required|exists:branches,id_br',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'id_table_type' => 'required|exists:table_types,id',
            ], [
                'guest_name.required' => 'Tên khách hàng là bắt buộc',
                'guest_phone.required' => 'Số điện thoại là bắt buộc',
                'guest_phone.regex' => 'Số điện thoại không hợp lệ',
                'guest_email.required' => 'Email là bắt buộc',
                'guest_email.email' => 'Email không đúng định dạng',
                'id_br.required' => 'Vui lòng chọn chi nhánh',
                'id_br.exists' => 'Chi nhánh không tồn tại',
                'date.required' => 'Vui lòng chọn ngày đặt',
                'date.after_or_equal' => 'Ngày đặt phải là ngày hôm nay hoặc sau',
                'time.required' => 'Vui lòng chọn giờ đặt',
                'time.date_format' => 'Giờ không đúng định dạng',
                'id_table_type.required' => 'Vui lòng chọn loại bàn',
                'id_table_type.exists' => 'Loại bàn không tồn tại',
            ]);

            $dateTime = $request->date . ' ' . $request->time;

            $bookingTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

            $bookedTableIds = Books::where('time', $bookingTime)
                ->whereIn('status', [0, 1]) // holding hoặc confirmed
                ->pluck('id_table'); //lay 1-2 cot 

            $table = Table::where('id_br', $request->id_br)
                ->where('table_type_id', $request->id_table_type)
                ->whereNotIn('id_table', $bookedTableIds)
                ->first();

            //Tạo booking
            $booking = Books::create([
                'id_br' => $request->id_br ?? 1,
                'id_table' => $table->id_table,
                'time' => $bookingTime,
                'status' => 1,
                'id_table_type' => $request->id_table_type ?? null,
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'guest_email' => $request->guest_email,
            ]);

            // return response()->json([
            //     dd($booking)
            // ]);

            if ($booking) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tạo đặt bàn thành công!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng thử lại sau',
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

    public function confirm(int $id)
    {
        try {
            $book = Books::findOrFail($id);
            $book->status = 1; // confirmed
            $book->save();

            return redirect()->back()->with('success', 'Xác nhận đặt bàn thành công');
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }

    public function cancel(int $id)
    {
        try {
            $book = Books::findOrFail($id);
            $book->status = 3; // cancelled
            $book->save();

            return redirect()->back()->with('success', 'Hủy đặt bàn thành công');
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }

    public function show($id)
    {
        $book = Books::with(['customer', 'table', 'branch', 'tableType'])->findOrFail($id);
        return view('admin.pages.book_show', compact('book'));
    }

    public function edit(int $id)
    {
        $book = Books::with(['customer', 'table', 'branch', 'tableType'])->findOrFail($id);
        $tables = Table::all()
                ->where('id_br', '=', $book->branch->id_br)
                ->where('table_type_id', '=', $book->tableType->id)
                ->whereIn('status', [0,1]);
        $branches = Branch::all();
        $tableTypes = TableTypes::all();
        return view('admin.pages.book_edit', compact('book', 'branches', 'tableTypes', 'tables'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                'guest_email' => 'required|email',
                'id_br' => 'required|exists:branches,id_br',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'id_table_type' => 'required|exists:table_types,id',
            ], [
                'guest_name.required' => 'Tên khách hàng là bắt buộc',
                'guest_phone.required' => 'Số điện thoại là bắt buộc',
                'guest_phone.regex' => 'Số điện thoại không hợp lệ',
                'guest_email.required' => 'Email là bắt buộc',
                'guest_email.email' => 'Email không đúng định dạng',
                'id_br.required' => 'Vui lòng chọn chi nhánh',
                'id_br.exists' => 'Chi nhánh không tồn tại',
                'date.required' => 'Vui lòng chọn ngày đặt',
                'date.after_or_equal' => 'Ngày đặt phải là ngày hôm nay hoặc sau',
                'time.required' => 'Vui lòng chọn giờ đặt',
                'time.date_format' => 'Giờ không đúng định dạng',
                'id_table_type.required' => 'Vui lòng chọn loại bàn',
                'id_table_type.exists' => 'Loại bàn không tồn tại',
                
            ]);

        try {
            $book = Books::findOrFail($id);
            $dateTime = $request->date . ' ' . $request->time;

            // Kiểm tra trạng thái hiện tại
            $currentStatus = $book->status;
            $newStatus = $request->status;

            // Không cho thay đổi trạng thái nếu đã hoàn thành (3)
            if ($currentStatus == 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn đặt bàn đã hoàn thành, không thể thay đổi',
                ]);
            }

            // Không cho thay đổi trạng thái nếu đã hủy (3), trừ khi khôi phục về Holding (0)
            if ($currentStatus == 3 && $newStatus != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn đặt bàn đã hủy, không thể thay đổi (chỉ có thể khôi phục về trạng thái chờ)',
                ]);
            }

            // Tự động chọn bàn
            // Nếu đổi loại bàn hoặc ngày giờ, tìm bàn mới
            $needNewTable = $request->id_table_type != $book->id_table_type
                || \Carbon\Carbon::parse($book->time)->format('Y-m-d') != $request->date
                || \Carbon\Carbon::parse($book->time)->format('H:i') != $request->time;

            $tableId = $book->id_table;

            if ($needNewTable) {
                // Lấy danh sách bàn đã đặt (trừ bàn hiện tại của booking này)
                $bookedTableIds = Books::where('id_br', $request->id_br)
                    ->whereDate('time', $request->date)
                    ->whereTime('time', $request->time)
                    ->whereIn('status', [0, 1])
                    ->where('id_book', '!=', $id)
                    ->pluck('id_table');

                // Tìm bàn trống theo loại bàn mới
                $table = Table::where('id_br', $request->id_br)
                    ->where('table_type_id', $request->id_table_type)
                    ->whereNotIn('id_table', $bookedTableIds)
                    ->first();

                if (!$table) {
                    return redirect()->back()->with('error', 'Không còn bàn trống cho loại bàn này vào thời gian đã chọn')->withInput();
                }

                $tableId = $table->id_table;
            }

            $update = $book->update([
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'guest_email' => $request->guest_email,
                'id_br' => $request->id_br,
                'id_table' => $tableId,//toan tu 3 ngoi viet tat
                'id_table_type' => $request->id_table_type , 
                'time' => $dateTime,
                'status' => $request->status,
            ]);

            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật đặt bàn thành công',
                    'redirect' => $request->status == 0 || $request->status == 1 ? route('admin.book.edit', $id) : route('admin.book')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng thử lại sau',
                ]);
            }

            //return redirect()->route('admin.book')->with('success', 'Cập nhật đặt bàn thành công');
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function destroy(int $id)
    {
        try {
            $book = Books::findOrFail($id);
            $book->delete();

            return redirect()->route('admin.book')->with('success', 'Xóa đặt bàn thành công');
        } catch (\Exception) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra');
        }
    }
}
