<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">

<!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Mây Nướng</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            {{-- Hiển thị tên & chức vụ của admin đang đăng nhập --}}
            @isset($authAdmin)
                <div class="sidebar-heading px-3 mt-2 mb-0" style="font-size: .75rem; color: rgba(255,255,255,.6);">
                    {{-- <i class="fas fa-user-circle"></i>  --}}
                    {{ $authAdmin->name ?? 'Admin' }}
                </div>
                <div class="px-3 mb-2" style="font-size: .7rem; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .05em;">
                    <span class="badge badge-warning">{{ $authAdmin->role_name ?? '—' }}</span>
                </div>
            @endisset

            <!-- Nav Item - Dashboard -->
            @if($canAccess(\App\Helpers\PermissionHelper::MODULE_DASHBOARD))
            <li class="nav-item active">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            @endif

            <!-- Divider -->
            <hr class="sidebar-divider">

            {{-- Nhóm "Quản lý" chỉ hiện khi user có ít nhất 1 quyền trong nhóm này --}}
            @php
                $showQuanLy = $canAccess(\App\Helpers\PermissionHelper::MODULE_BOOKING)
                    || $canAccess(\App\Helpers\PermissionHelper::MODULE_MENU)
                    || $canAccess(\App\Helpers\PermissionHelper::MODULE_CUSTOMER)
                    || $canAccess(\App\Helpers\PermissionHelper::MODULE_KITCHEN)
                    || $canAccess(\App\Helpers\PermissionHelper::MODULE_EMPLOYEE);
            @endphp
            @if($showQuanLy)
            <!-- Heading -->
            <div class="sidebar-heading">
                Quản lý
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                {{-- Đặt bàn --}}
                @if($canAccess(\App\Helpers\PermissionHelper::MODULE_BOOKING))
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTable"
                    aria-expanded="true" aria-controls="collapseTable">
                    <i class="fas fa-fw fa-chair"></i>
                    <span>Quản lý đặt bàn</span>
                </a>
                <div id="collapseTable" class="collapse" aria-labelledby="headingTable" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.book') }}">Danh sách đặt bàn</a>
                        <a class="collapse-item" href="{{ route('admin.book.create') }}">Tạo đặt bàn</a>
                        <a class="collapse-item" href="{{ route('admin.table') }}">Quản lý bàn</a>
                    </div>
                </div>
                @endif

                {{-- Thực đơn --}}
                @if($canAccess(\App\Helpers\PermissionHelper::MODULE_MENU))
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseMenu"
                    aria-expanded="true" aria-controls="collapseMenu">
                    <i class="fas fa-fw fa-utensils"></i>
                    <span>Quản lý thực đơn</span>
                </a>
                <div id="collapseMenu" class="collapse" aria-labelledby="headingMenu" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.menuItem') }}">Danh sách món ăn</a>
                        <a class="collapse-item" href="{{ route('admin.category') }}">Danh mục món ăn</a>
                        <a class="collapse-item" href="{{ route('admin.menuItem.create') }}">Thêm món mới</a>
                    </div>
                </div>
                @endif

                {{-- Khách hàng --}}
                @if($canAccess(\App\Helpers\PermissionHelper::MODULE_CUSTOMER))
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer"
                    aria-expanded="true" aria-controls="collapseCustomer">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Quản lý khách hàng</span>
                </a>
                <div id="collapseCustomer" class="collapse" aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.customer') }}">Danh sách khách hàng</a>
                        <a class="collapse-item" href="{{ route('admin.customer.create') }}">Thêm khách hàng</a>
                        <a class="collapse-item" href="{{ route('admin.bill.index') }}">Hóa đơn</a>
                    </div>
                </div>
                @endif

                {{-- Nhân viên --}}
                @if($canAccess(\App\Helpers\PermissionHelper::MODULE_EMPLOYEE))
                <a class="nav-link collapsed" href="#" data-toggle="collapse"  data-target="#collapseEmployee"
                    aria-expanded="true" aria-controls="collapseEmployee">
                    <i class="fas fa-fw fa-user-tie"></i>
                    <span>Quản lý nhân viên</span>
                </a>
                <div id="collapseEmployee" class="collapse" aria-labelledby="headingEmployee" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.user') }}">Danh sách nhân viên</a>
                        <a class="collapse-item" href="{{ route('admin.user.create') }}">Thêm nhân viên</a>
                    </div>
                </div>
                @endif

                {{-- Gọi món (phục vụ + bếp xem order) --}}
                @if($canAccess(\App\Helpers\PermissionHelper::MODULE_ORDER) || $canAccess   (\App\Helpers\PermissionHelper::MODULE_KITCHEN))
                
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOrder"
                    aria-expanded="true" aria-controls="collapseOrder">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Gọi món</span>
                </a>
                <div id="collapseOrder" class="collapse" aria-labelledby="headingOrder" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if($canAccess(\App\Helpers\PermissionHelper::MODULE_ORDER))
                        <h6 class="collapse-header">Phục vụ:</h6>
                        <a class="collapse-item" href="{{ route('admin.order.tables') }}">Tạo đơn gọi món</a>
                        <a class="collapse-item" href="{{ route('admin.order.management') }}">Quản lý order theo bàn</a>
                        @endif
                        @if($canAccess(\App\Helpers\PermissionHelper::MODULE_KITCHEN))
                        <a class="collapse-item" href="{{ route('admin.kitchen') }}">Xem order</a>
                        @endif
                    </div>
                </div>
                @endif
            </li>
            @endif

            @if($canAccess(\App\Helpers\PermissionHelper::MODULE_BRANCH))
            {{-- Quản lý chi nhánh - nằm trong module BOOKING (đặt bàn / bàn / chi nhánh) --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBranch"
                    aria-expanded="true" aria-controls="collapseBranch">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Quản lý chi nhánh</span>
                </a>
                <div id="collapseBranch" class="collapse" aria-labelledby="headingBranch" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.branch') }}">Danh sách chi nhánh</a>
                        <a class="collapse-item" href="{{ route('admin.branch.create') }}">Thêm chi nhánh</a>
                    </div>
                </div>
            </li>
            @endif

            {{-- Kho nguyên liệu --}}
            @if($canAccess(\App\Helpers\PermissionHelper::MODULE_INVENTORY))
            <!-- Divider -->
            <hr class="sidebar-divider">
            <div class="sidebar-heading">
                Kho nguyên liệu
            </div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInventory"
                    aria-expanded="true" aria-controls="collapseInventory">
                    <i class="fas fa-fw fa-warehouse"></i>
                    <span>Kho nguyên liệu</span>
                </a>
                <div id="collapseInventory" class="collapse" aria-labelledby="headingInventory" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các chức năng:</h6>
                        <a class="collapse-item" href="{{ route('admin.supplier') }}">Nhà cung cấp</a>
                        <a class="collapse-item" href="{{ route('admin.ingredient') }}">Danh sách nguyên liệu</a>
                        {{-- <a class="collapse-item" href="{{ route('admin.inventory.dashboard') }}"><i class="fas fa-chart-line text-primary mr-1"></i> Tổng quan tồn kho</a> --}}
                        <a class="collapse-item" href="{{ route('admin.inventory.stock') }}">Tồn kho</a>
                        <a class="collapse-item" href="{{ route('admin.inventory.forecast') }}">Dự báo nhập kho</a>
                        <a class="collapse-item" href="{{ route('admin.import.index') }}">Nhập kho</a>
                        <a class="collapse-item" href="{{ route('admin.export.index') }}">Xuất kho</a>
                        <a class="collapse-item" href="{{ route('admin.transfer.index') }}"></i> Chuyển kho</a>
                        <a class="collapse-item" href="{{ route('admin.stock_check.index') }}">Kiểm kê</a>
                    </div>
                </div>
            </li>
            @endif

            {{-- Báo cáo --}}
            @if($canAccess(\App\Helpers\PermissionHelper::MODULE_REPORT))
            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Báo cáo
            </div>

            <!-- Nav Item - Reports -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport"
                    aria-expanded="true" aria-controls="collapseReport">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Báo cáo</span>
                </a>
                <div id="collapseReport" class="collapse" aria-labelledby="headingReport" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Các loại báo cáo:</h6>
                        <a class="collapse-item" href="{{ route('admin.report.index') }}">
                            <i class="fas fa-chart-line text-primary mr-1"></i> Tổng quan
                        </a>
                        {{-- <a class="collapse-item" href="{{ route('admin.report.best_selling') }}">
                            <i class="fas fa-fire text-danger mr-1"></i> Món ăn bán chạy
                        </a> --}}
                        {{-- <a class="collapse-item" href="{{ route('admin.report.ingredients') }}">
                            <i class="fas fa-carrot text-warning mr-1"></i> Nguyên liệu
                        </a> --}}
                    </div>
                </div>
            </li>
            @endif

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->

        <div id="content-wrapper" class="d-flex flex-column">
