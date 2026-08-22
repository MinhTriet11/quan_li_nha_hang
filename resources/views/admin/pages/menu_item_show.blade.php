@extends('layouts.admin_home')

@section('title', 'Chi tiết món ăn')

@section('content')

    <div id="content">
        @include('admin.partials.topbar')

        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Chi tiết món ăn</h1>
                <a href="{{ route('admin.menuItem') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin món ăn</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if ($menuItem->image)
                                <img src="{{ asset('storage/menu_items/' . $menuItem->image) }}" alt="{{ $menuItem->name }}"
                                    style="width: 100%; max-width: 300px; object-fit: cover;">
                            @else
                                <img src="{{ asset('assets/admin/img/no-image.png') }}" alt="No image"
                                    style="width: 100%; max-width: 300px; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tr>
                                    <th width="150">Tên món ăn:</th>
                                    <td>{{ $menuItem->name }}</td>
                                </tr>
                                <tr>
                                    <th>Danh mục:</th>
                                    <td>{{ $menuItem->category->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Giá:</th>
                                    <td>{{ number_format($menuItem->price, 0, ',', '.') }} VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái:</th>
                                    <td>
                                        @switch($menuItem->status)
                                            @case(0)
                                                <span class="badge badge-success">Hoạt động</span>
                                            @break

                                            @case(1)
                                                <span class="badge badge-secondary">Ngừng bán</span>
                                            @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mô tả:</th>
                                    <td>{{ $menuItem->description ?? 'Không có mô tả' }}</td>
                                </tr>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Công thức</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên nguyên liệu</th>
                                    <th>Đơn vị</th>
                                    <th width="10%">Định lượng</th>
                                    <th width="30%">Ghi chú</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recipes as $key => $value)
                                    <tr>
                                        <td>{{ $key + 1 + ($recipes->currentPage() - 1) * $recipes->perPage() }}</td>
                                        <td>{{ $value->ingredient->name }}</td>
                                        <td>{{ $value->ingredient->unit }}</td>
                                        <td class="text-center"><input id="recipeQuantity" class="recipeQuantity form-control" type="text" data-id ="{{ $value->id_mii }}" value="{{ $value->quantity }}"></td>
                                        <td>{{ $value->note }}</td>
                                        <td class="text-center" width="10%">
                                            <form class="d-inline" action="{{ route('admin.recipe.destroy', $value->id_mii) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Xóa"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa nguyên liệu này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>Không có dữ liệu công thức</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($recipes->hasPages())
                            <div class="mt-3 d-flex justify-content-end align-items-center pagination-sm">
                                {{-- appends giu lai url --}}
                                {{ $recipes->links() }}  
                            </div>
                            @endif
                        <form id="addRecipeItem" method="POST" action="{{ route('admin.recipe.store') }}">
                            @csrf
                            <input type="hidden" name="id_food" value="{{ $menuItem->id_food }}">
                            <div class="row">
                                <div class="form-group col-3">
                                    <select name="id_ingre" id="selectedIngre" class="form-control" rules="required">
                                        <option value="">----- Chọn nguyên liệu -----</option>
                                        @foreach ( $ingredients as $ingredient)
                                            <option value="{{ $ingredient->id_ingre }}" data-unit="{{ $ingredient->unit }}">{{ $ingredient->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group col-2">
                                    <input type="text" class="form-control" placeholder="Đơn vị" value="g" id="unitIngreInput" readonly>
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group col-2">
                                    <input type="number" step="any" class="form-control" placeholder="Nhập số lượng" value="" name="quantity" id="recipeQuantity" rules="required">
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group col-3">
                                    <input type="text" class="form-control" placeholder="Ghi chú" value="" name="note">
                                    <span class="form-message"></span>
                                </div>

                                <div class="form-group col-2">
                                    <button type="submit" class="btn btn-primary">Thêm</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- @if ($users->hasPages())
                                        <div class="mt-3 d-flex justify-content-end align-items-center">
                                            {{ $users->appends(request()->query())->links() }}
                                        </div>
                                    @endif --}}
                </div>
            </div>
        </div>
    </div>

@endsection
