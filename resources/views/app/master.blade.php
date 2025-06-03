@extends('app.layout')
@section('content')
    @include('app.navbar')
    <div class="container mt-5">
        @yield('mastercontent')
    </div>
@endsection
@push('plugin-scripts')
    <script src="{{ asset('asset/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('asset/datatable/dataTables.min.js') }}"></script>
    <script src="{{ asset('asset/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/sweetalert/sweetalert.min.js') }}"></script>
@endpush

