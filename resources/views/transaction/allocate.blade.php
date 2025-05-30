@extends('app.layout')
@section('content')
    <h1>Allocate</h1>
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white fs-4">
            Allocate Form
        </div>
        <form action="" id="allocateForm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle-number" class="form-label">Vehicle Number</label>
                            <input type="text" class="form-control" name="vehicle_number" id="vehicle-number" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle" class="form-label">Vehicle:</label>
                            <select class="form-control" name="vehicle_id" id="vehicle" required>
                                <option value="" selected disabled>--Select Vehicle--</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="location" class="form-label">Location:</label>
                        <select class="form-control" name="location_id" id="location" required>
                            <option value="" selected disabled>--Select Location--</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between">
                        <h5>Space Occupancy</h5>
                        <div id="outOf">

                        </div>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-secondary text-white" id="availableBar" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            Select Location
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" id="allocateButton">
                    <span id="loadingText" style="display: none;">Allocating...</span>
                    <span id="submitText">Allocate</span>
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-auto">
        <table class="table table-striped mt-3" id="dataTable">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Vehicle Number</th>
                    <th scope="col">Location</th>
                    <th scope="col">Status</th>
                    <th scope="col">In Time</th>
                    <th scope="col">Out Time</th>
                    <th scope="col">Barcode</th>
                    <th>Get Print</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($allocates as $allocate)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$allocate->vehicle_number}}</td>
                        <td>{{$allocate->location->name}}</td>
                        <td>{{$allocate->status}}</td>
                        <td>{{$allocate->in_time}}</td>
                        <td>{{$allocate->out_time}}</td>
                        <td>{{$allocate->barcode}}</td>
                        <td><a href="{{route('allocate.getprint', $allocate->id)}}" target="_blank"><button class="btn btn-info btn-sm">Get Print</button></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No Data Found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
@section('script')
<script>

    document.getElementById('vehicle').addEventListener('change', function(e) {
        const vehicleid = document.getElementById('vehicle').value;

        axios.post('{{ route('allocate.fetchlocations') }}', {
            vehicleId: vehicleid
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            const data = response.data;

            if (data.status === 200) {
                const locationSelect = document.getElementById('location');
                locationSelect.innerHTML = '<option value="" selected disabled>--Select Location--</option>';

                if (data.locations && data.locations.length > 0) {
                    data.locations.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.id;
                        option.textContent = location.name;
                        locationSelect.appendChild(option);
                    });
                } else {
                    locationSelect.innerHTML = '<option value="" selected disabled>--Select Location--</option><option value="" disabled>--No Locations Found--</option>';
                }
            } else {
                console.error('Failed to Fetch Locations:', data.message || 'Unknown error');
                locationSelect.innerHTML = '<option value="" selected disabled>--Select Location--</option><option value="" disabled>--Error Loading Data--</option>';
            }
        })
        .catch(error => {
            console.error('Error fetching locations:', error);
            const locationSelect = document.getElementById('location');
            locationSelect.innerHTML = '<option value="" selected disabled>--Select Location--</option><option value="" disabled>--Error Loading Data--</option>';
        });
    });

    document.getElementById('location').addEventListener('change', function(e) {
        const locationId = document.getElementById('location').value;

        axios.post('{{route('allocate.getslots')}}', {
            locationId : locationId
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log(response);
            const data = response.data

            if(data.status === 200){
                if(data.slots.total_slots <= data.slots.slots_left){
                    $('#allocateButton').prop('disabled', true);
                    $('#outOf').html('');
                    $('#outOf').append(`${data.slots.slots_left} / ${data.slots.total_slots} slots`);
                    updateProgressBar(data.slots.total_slots, data.slots.slots_left);
                }else{
                    $('#allocateButton').prop('disabled', false);
                    $('#outOf').html('');
                    $('#outOf').append(`${data.slots.slots_left} / ${data.slots.total_slots} slots`);
                    updateProgressBar(data.slots.total_slots, data.slots.slots_left);
                }
            }else{
                console.error("Failed to Fetch Slosts");
            }
        })
        .catch(error => {
            console.error('Error fetching locations:', error);
        })
    });

    document.getElementById('allocateForm').addEventListener('submit', function(e){
        e.preventDefault();

        const vehicleNumber = document.getElementById('vehicle-number').value;
        const vehicleId = document.getElementById('vehicle').value;
        const locationId = document.getElementById('location').value;

        if (!vehicleNumber || !vehicleId || !locationId) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please enter all the required fields.',
                confirmButtonText: 'OK'
            });
        }

        axios.post('{{route('allocate.store')}}', {
            vehicleNumber : vehicleNumber,
            vehicleId : vehicleId,
            locationId : locationId,

        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log(response);

        })

    });

    function updateProgressBar(totalSlots, slotsLeft) {
        const percentageLeft = (slotsLeft / totalSlots) * 100;

        const progressBar = document.getElementById('availableBar');

        // Update progress bar styles and text
        progressBar.style.width = `${percentageLeft}%`;
        progressBar.setAttribute('aria-valuenow', percentageLeft.toFixed(0));
        progressBar.textContent = `Space Occupied: ${percentageLeft.toFixed(0)}%`;

        // Change bar color based on the percentage left
        if (percentageLeft < 40) {
            progressBar.classList.remove('bg-danger', 'bg-warning');
            progressBar.classList.add('bg-success');
        } else if (percentageLeft < 70) {
            progressBar.classList.remove('bg-success', 'bg-danger');
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.remove('bg-warning', 'bg-success');
            progressBar.classList.add('bg-danger');
        }
    }
</script>
@endsection
