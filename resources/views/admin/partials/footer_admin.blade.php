

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }} "></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src=" {{ asset('assets/admin/vendor/jquery-easing/jquery.easing.min.js') }} "></script>

    <!-- Custom scripts for all pages-->
    <script src=" {{ asset('assets/admin/js/sb-admin-2.min.js') }} "></script>

    <!-- Page level plugins -->
    <script src=" {{ asset('assets/admin/vendor/chart.js/Chart.min.js') }} "></script>

    @php
        // Các trang còn dùng chart demo mặc định của SB Admin 2 (canvas id myAreaChart / myPieChart)
        // sẽ push 'demo-charts' để include file demo JS ở đây.
        // Trang dashboard tự vẽ chart nên KHÔNG push, tránh demo script null-ref và phá JS trang.
    @endphp
    @stack('demo-charts')

    <script src="{{ asset('assets/admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script type="module" src="{{ asset('assets/admin/js/main.js') }}"></script>


    @yield('js')
    @stack('js')

    @flasher_render
</body>

</html>