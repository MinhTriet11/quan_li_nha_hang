<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\TableTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(){
        $categories = Category::with('menuItems')->get();

        // Lấy 4 món nướng
        $grilledItems = MenuItem::limit(4)
                    ->where('id_cate', '=' , 1)
                    ->get();

        //lay chi nhanh
        $branches = Branch::all();
        $branchesImage = $branches->take(3);

        // Lấy thông tin khách hàng đã đăng nhập
        $customer = Auth::guard('customer')->user();

        // Lấy thông tin các loại bàn
        // $tableTypes = TableTypes::all();


        return view('client.pages.home', compact('categories', 'grilledItems', 'branches', 'branchesImage' , 'customer' ));
    }
}
