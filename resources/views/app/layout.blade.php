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
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{route('dashboard')}}">ParkingLot</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Master
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{route('master.vehicle.index')}}">Vehicle Management</a></li>
                        <li><a class="dropdown-item" href="{{route('master.location.index')}}">Location Management</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Transaction
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{route('transaction.allocate.index')}}">Allocate</a></li>
                        <li><a class="dropdown-item" href="{{route('transaction.scan.index')}}">Scan Out</a></li>
                    </ul>
                </li>
            </ul>
                <a href="{{route('logout')}}"><button class="btn btn-outline-danger" type="submit">Logout</button></a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        @yield('content')
    </div>

    <script src="{{asset('asset/jquery/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/axios/axios.min.js')}}"></script>
    <script src="{{asset('asset/datatable/dataTables.min.js')}}"></script>
    <script src="{{asset('asset/bootstrap/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('asset/js/sweetalert/sweetalert.min.js')}}"></script>

    @yield('script')
</body>
</html>
