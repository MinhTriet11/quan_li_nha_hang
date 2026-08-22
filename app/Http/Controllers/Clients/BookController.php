<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Books;
use App\Models\Customer;
use App\Models\Table;
use App\Models\TableTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookController extends Controller {

    public function reserVation(Request $request)
    { 
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|regex:/^(0[35789])[0-9]{8}$/',
                'email' => 'required|email',
                'branch' => 'required|exists:branches,id_br',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'id_table_type' => 'required|exists:table_types,id',
            ], [
                'name.required' => 'Tên khách hàng là bắt buộc',
                'phone.required' => 'Số điện thoại là bắt buộc',
                'phone.regex' => 'Số điện thoại không hợp lệ',
                'email.required' => 'Email là bắt buộc',
                'email.email' => 'Email không đúng định dạng',
                'branch.required' => 'Vui lòng chọn chi nhánh',
                'branch.exists' => 'Chi nhánh không tồn tại',
                'date.required' => 'Vui lòng chọn ngày đặt',
                'date.after_or_equal' => 'Ngày đặt phải là ngày hôm nay hoặc sau',
                'time.required' => 'Vui lòng chọn giờ đặt',
                'time.date_format' => 'Giờ không đúng định dạng',
                'id_table_type.required' => 'Vui lòng chọn loại bàn',
                'id_table_type.exists' => 'Loại bàn không tồn tại',
            ]);

            $customer = Auth::guard('customer')->user();

            $dateTime = $request->date .' '. $request->time;

            $bookingTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

            // Moi luot dat chiem 2 gio (gio dat -> gio dat + 2h).
            // Khung gio T bi chiem neu co bat ky booking nao trong khoang [T-2h, T+2h].
            // VD: dat 18h se chan cac booking trong khoang 16h-20h.
            $windowStart = $bookingTime->copy()->subHours(2);
            $windowEnd = $bookingTime->copy()->addHours(2);

            $bookedTableIds = Books::where('id_br', $request->branch)
                ->whereIn('status', [0, 1]) // holding hoặc confirmed
                ->whereBetween('time', [$windowStart, $windowEnd])
                ->pluck('id_table'); //lay 1-2 cot

            $table = Table::where('id_br', $request->branch)
                ->where('table_type_id', $request->id_table_type)
                ->whereNotIn('id_table', $bookedTableIds)
                ->first();
            
            if(empty($table)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đã hết bàn lúc '. $bookingTime .', vui lòng chọn khung giờ khác',
                ]);
            }

            //Tạo booking
            $booking = Books::create([
                'id_cus' => $customer->id_cus ?? null,
                'id_br' => $request->branch ?? 1,
                'id_table' => $table->id_table,
                'time' => $bookingTime,
                'status' => 1,
                'id_table_type' => $request->id_table_type ?? null,
                'guest_name' => $request->name,
                'guest_phone' => $request->phone,
                'guest_email' => $request-> email,
            ]);

            if ($booking) {
                // $result = Table::where('id_table', $table->id_table)->update(['status' => 1]);
                    return response()->json([
                    'status' => 'success',
                    'message' => 'Đặt bàn thành công',
                    'data' => $booking
                ]);
                
                
            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vui lòng thử lại sau',
                ]);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

    public function getTableType($branchId)
    {
        $tableTypes = TableTypes::withCount([
            'tables as available_count' => function ($query) use ($branchId) {
                $query->where('id_br', $branchId)
                    ->where('status', '0');
            }
        ])->get();
        return response()->json($tableTypes);
    }

    public function getAvailableTables(Request $request)
    {
        $branchId = $request->branch;
        $date = $request->date;
        $time = $request->time;

        if (!$branchId || !$date || !$time) {
            return response()->json(['tables' => []]);
        }

        $dateTime = $date . ' ' . $time;
        $bookingTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

        // Moi luot dat chiem 2 gio (gio dat -> gio dat + 2h).
        // Khi kiem tra ban trong cho khung gio T, can loai cac ban da co
        // booking trong khoang [T-2h, T+2h] de tranh xung dot.
        $windowStart = $bookingTime->copy()->subHours(2);
        $windowEnd = $bookingTime->copy()->addHours(2);

        
        $bookedTableIds = Books::where('id_br', $branchId)
            ->whereBetween('time', [$windowStart, $windowEnd])
            ->whereIn('status', [0, 1]) // pending or confirmed
            ->pluck('id_table');

        // Lấy các bàn của chi nhánh, trừ các bàn đã được đặt
        $availableTables = Table::where('id_br', $branchId)
            ->whereNotIn('id_table', $bookedTableIds)
            ->selectRaw('table_type_id, COUNT(*) as available_count')
            ->groupBy('table_type_id')
            ->get();

        // Lấy thông tin loại bàn và merge vào kết quả
        $tableTypeIds = $availableTables->pluck('table_type_id')->unique();
        $tableTypes = TableTypes::whereIn('id', $tableTypeIds)->pluck('name', 'id');

        $availableTables = $availableTables->map(function ($item) use ($tableTypes) { //map duyet qua tung loai ban
            $item->table_type_name = $tableTypes[$item->table_type_id] ?? null; //gan name vao 
            return $item;
        });

        return response()->json([
                'data' => $availableTables
            ]);
    }

    /**
     * Tra ve cac khung gio (HH:MM) trong ngay da bi chiem,
     * moi luot dat chiem 2h nen mot booking luc 18h se chan
     * cac khung gio trong khoang 16:00-20:00.
     */
    public function getBookedSlots(Request $request)
    {
        $branchId = $request->branch;
        $date = $request->date;

        if (!$branchId || !$date) {
            return response()->json(['slots' => []]);
        }

        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // Lay tat ca booking trong ngay
        $bookings = Books::where('id_br', $branchId)
            ->whereBetween('time', [$startOfDay, $endOfDay])
            ->whereIn('status', [0, 1])
            ->pluck('time');

        // Voi moi booking, tinh tat ca cac khung 30 phut trong
        // khoang [time-2h, time+2h] (gio mo cua hang 10:00-22:00)
        $bookedSlots = [];
        foreach ($bookings as $bookingTime) {
            $bookingCarbon = Carbon::parse($bookingTime);
            $windowStart = $bookingCarbon->copy()->subHours(2);
            $windowEnd = $bookingCarbon->copy()->addHours(2);

            $cursor = $windowStart->copy();
            while ($cursor->lt($windowEnd)) {
                $bookedSlots[] = $cursor->format('H:i');
                $cursor->addMinutes(30);
            }
        }

        return response()->json([
            'slots' => array_values(array_unique($bookedSlots))
        ]);
    }
}
