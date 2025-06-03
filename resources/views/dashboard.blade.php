@extends('app.master')

@section('mastercontent')
<div class="container mt-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h1>Dashboard</h1>
        </div>
        <div class="col-md-6 text-md-end">
            <select class="form-control w-100 w-md-auto d-inline-block" id="vehicle">
                <option value="">All Vehicles</option>
                @forelse ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                @empty
                    <option value="" disabled>No Vehicles Found</option>
                @endforelse
            </select>
        </div>
    </div>

    <div class="row" id="locationContainer">
        <!-- Cards will be injected here -->
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    let charts = {};

    function createCard(location) {
        return `
            <div class="col-md-3 mb-4">
                <div class="card shadow-lg border-light rounded hover-shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="font-weight-bold">${location.name}</div>
                        <div class="text-muted">${location.allocated} / ${location.totalSlot}</div>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="locationChart${location.id}" width="200" height="200"></canvas>
                    </div>
                </div>
            </div>
        `;
    }

    function createPieChart(canvasId, allocated, available) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        if (charts[canvasId]) charts[canvasId].destroy();

        charts[canvasId] = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Allocated', 'Available'],
                datasets: [{
                    data: [allocated, available],
                    backgroundColor: ['#f44336', '#4CAF50'],
                    borderColor: ['#f44336', '#4CAF50'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    }

    function renderLocations(locations) {
        const container = document.getElementById('locationContainer');
        container.innerHTML = '';

        if (locations.length === 0) {
            container.innerHTML = '<div class="col-12 text-center text-muted">No locations available.</div>';
            return;
        }

        locations.forEach(loc => {
            container.innerHTML += createCard(loc);
        });

        locations.forEach(loc => {
            createPieChart(`locationChart${loc.id}`, loc.allocated, loc.available);
        });
    }

    function fetchLocations(vehicleId = null) {
        axios.post('{{ route('locations') }}', {
            vehicleId: vehicleId
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => {
            if (res.data.status === 200) {
                renderLocations(res.data.data);
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Failed to load locations',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        fetchLocations(); // Load all on page load

        document.getElementById('vehicle').addEventListener('change', function () {
            const vehicleId = this.value || null;
            fetchLocations(vehicleId);
        });
    });
</script>
@endpush
