@extends('app.layout')
@section('content')
    <div class="container mt-5">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h1 class="mb-3">ParkingLot</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <select class="form-control w-100 w-md-auto d-inline-block" name="vehicle" id="vehicle">
                    <option value="">All Vehicles</option>
                    @forelse ($vehicles as $vehicle)
                        <option value="{{$vehicle->id}}">{{$vehicle->name}}</option>
                    @empty
                        <option value="" disabled>No Vehicles Found</option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="row" id="locationContainer"></div>
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

        if (charts[canvasId]) {
            charts[canvasId].destroy();
        }

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
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    function renderLocations(locations) {
        const container = document.getElementById('locationContainer');
        container.innerHTML = ''; // Clear previous cards

        locations.forEach(location => {
            container.innerHTML += createCard(location);
        });

        // After DOM elements are added, initialize charts
        locations.forEach(location => {
            createPieChart(`locationChart${location.id}`, location.allocated, location.available);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const vehicleSelect = document.getElementById('vehicle');

        // Initial load: trigger with no vehicle selected
        loadLocations(null);

        vehicleSelect.addEventListener('change', function () {
            const vehicleId = this.value || null;
            loadLocations(vehicleId);
        });

        function loadLocations(vehicleId) {
            axios.post('{{ route('locations') }}', {
                vehicleId: vehicleId
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (response.data.status === 200) {
                    renderLocations(response.data.data);
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Something Went Wrong',
                    text: error.message || 'Unable to load locations',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
    });

</script>
@endpush
