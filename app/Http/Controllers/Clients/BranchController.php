<?php

namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index(){
        $branches = DB::table('branches')
        ->where('status', '1')
        ->get();
        return view('client.pages.branches', compact('branches'));
    }
}
