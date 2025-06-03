<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{route('dashboard')}}">ParkingLot</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            @if (auth()->user()->type == 'admin')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Master
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{route('master.vehicle.index')}}">Vehicle Management</a></li>
                        <li><a class="dropdown-item" href="{{route('master.location.index')}}">Location Management</a></li>
                    </ul>
                </li>
            @endif
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Scan
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{route('scan.scanin')}}">Scan In</a></li>
                    <li><a class="dropdown-item" href="{{route('scan.scanout')}}">Scan Out</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{route('report.report')}}">Report</a>
            </li>
        </ul>
            <a href="{{route('logout')}}"><button class="btn btn-outline-danger" type="submit">Logout</button></a>
        </div>
    </div>
</nav>
