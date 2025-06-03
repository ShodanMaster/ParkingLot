@extends('app.master')
@section('mastercontent')
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-center text-white fs-4">
            Scan In
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
        <table class="table table-striped mt-3" id="allocateTable">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Vehicle Number</th>
                    <th scope="col">Location</th>
                    <th scope="col">Status</th>
                    <th scope="col">In Time</th>
                    <th scope="col">Out Time</th>
                    <th scope="col">Qrcode</th>
                    <th>Get Print</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
@endsection
@push('custom-scripts')
<script>
    $(document).ready(function () {

        const table = $('#allocateTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('scan.getallocates') }}',
            pageLength: 5,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'vehicle_number', name: 'vehicle_number' },
                { data: 'location', name: 'location' },
                { data: 'status', name: 'status' },
                { data: 'in_time', name: 'in_time' },
                { data: 'out_time', name: 'out_time' },
                { data: 'qrcode', name: 'qrcode' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });

    document.getElementById('vehicle').addEventListener('change', function(e) {
        const vehicleid = document.getElementById('vehicle').value;
        resetProgressBar();
        axios.post('{{ route('scan.fetchlocations') }}', {
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

        axios.post('{{ route('scan.getslots') }}', {
            locationId: locationId
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            const data = response.data;

            if (data.status === 200) {
                const total = data.slots.total_slots;
                const occupied = data.slots.occupied_slots;
                const available = data.slots.available_slots;

                $('#outOf').html(`${occupied} / ${total} slots occupied`);

                $('#allocateButton').prop('disabled', available <= 0);

                updateProgressBar(total, occupied);

            } else {
                console.error("Failed to fetch slots.");
            }
        })
        .catch(error => {
            console.error('Error fetching slot data:', error);
        });
    });

    document.getElementById('allocateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const vehicleNumber = document.getElementById('vehicle-number').value;
        const vehicleId = document.getElementById('vehicle').value;
        const locationId = document.getElementById('location').value;

        const submitButton = document.getElementById('allocateButton');
        const loadingText = document.getElementById('loadingText');
        const submitText = document.getElementById('submitText');

        if (!vehicleNumber || !vehicleId || !locationId) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please enter all the required fields.',
                confirmButtonText: 'OK'
            });
            return;
        }

        submitButton.disabled = true;
        loadingText.style.display = 'inline-block';
        submitText.style.display = 'none';

        axios.post('{{route('scan.scanningin')}}', {
            vehicleNumber: vehicleNumber,
            vehicleId: vehicleId,
            locationId: locationId,
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.data.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.data.message || 'Data stored successfully!',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    document.getElementById('allocateForm').reset();
                    window.open(response.data.print_url, '_blank');
                    $('#allocateTable').DataTable().ajax.reload();

                    resetProgressBar();
                });
            }
            else if(response.data.status ===409){
                 Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: response.data.message || 'Already In',
                    confirmButtonText: 'Yes, continue',
                    cancelButtonText: 'Cancel',
                    showConfirmButton: true,
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed){
                        allocatedVehicle(vehicleNumber, vehicleId, locationId);
                    }
                    else{
                        return;
                    }
                });
            }
            else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.data.message || 'Something went wrong!',
                });
            }
        })
        .catch(error => {
            let message = 'An unexpected error occurred.';
            if (error.response && error.response.data && error.response.data.message) {
                message = error.response.data.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: message,
            });
        })
        .finally(() => {
            submitButton.disabled = false;
            loadingText.style.display = 'none';
            submitText.style.display = 'inline';
        });
    });

    function updateProgressBar(totalSlots, occupiedSlots) {
        const percentageOccupied = (occupiedSlots / totalSlots) * 100;

        const progressBar = document.getElementById('availableBar');

        progressBar.style.width = `${percentageOccupied}%`;
        progressBar.setAttribute('aria-valuenow', percentageOccupied.toFixed(0));
        progressBar.textContent = `${percentageOccupied.toFixed(0)}% Occupied`;

        // Update bar color based on % occupied
        progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');

        if (percentageOccupied < 40) {
            progressBar.classList.add('bg-success');
        } else if (percentageOccupied < 70) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-danger');
        }
    }

    function resetProgressBar(){
        const progressBar = document.getElementById('availableBar');
        progressBar.style.width = '100%';
        progressBar.setAttribute('aria-valuenow', 100);
        progressBar.textContent = 'Select Location';
        progressBar.className = 'progress-bar bg-secondary text-white';

        document.getElementById('outOf').innerHTML = '';
    }

    function allocatedVehicle(vehicleNumber, vehicleId, locationId){
        axios.post('{{route('scan.allocatedvehcile')}}', {
            vehicleNumber: vehicleNumber,
            vehicleId: vehicleId,
            locationId: locationId,
        }, {
            headers : {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then((response) => {

            if(response.data.status === 200){
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.data.message || 'Data stored successfully!',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    document.getElementById('allocateForm').reset();
                    window.open(response.data.print_url, '_blank');
                    $('#allocateTable').DataTable().ajax.reload();

                    resetProgressBar();
                });
            }else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.data.message || 'Something went wrong!',
                });
            }
        });
    }
</script>
@endpush
