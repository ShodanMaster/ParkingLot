<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkingLot</title>
    <link rel="stylesheet" href="{{asset('asset/datatable/dataTables.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{asset('asset/bootstrap/bootstrap.min.css')}}">
</head>
<body>
    @yield('content')

    <script src="{{ asset('asset/js/chart/chart.js') }}"></script>
    <script src="{{ asset('asset/js/axios/axios.min.js') }}"></script>
    @stack('plugin-scripts')
    @stack('custom-scripts')
</body>
</html>
