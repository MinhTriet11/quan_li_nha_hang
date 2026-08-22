@include('admin.partials.header_admin')
@include('admin.partials.sidebar')


@yield('content')

@include('admin.partials.part_footer_admin')
@include('admin.partials.footer_admin')

@section('css')
    <!-- Custom CSS -->
@endsection

@section('js')
    <!-- Custom JS -->
@endsection


