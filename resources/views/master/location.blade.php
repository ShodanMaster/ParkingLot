@extends('app.layout')
@section('content')
<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="addLocationModalLabel">Add Location</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addLocationForm">
        <div class="modal-body">
            <div class="mb-3">
                <label for="vehicleId" class="form-label">Vehicle</label>
                <select name="vehicleId" id="vehicleId" class="form-control" required>
                    <option value="">Select Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                    @endforeach

                </select>
            </div>
            <div class="mb-3">
                <label for="locationName" class="form-label">Location Name</label>
                <input type="text" class="form-control" id="locationName" name="locationName" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editLocationModalLabel">Edit Location</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editLocationForm">
        <input type="hidden" id="editId" name="id">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="editVehicleId" class="form-label">Vehicle</label>
                    <select name="vehicleId" id="editVehicleId" class="form-control" required>
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="locationName" class="form-label">Location Name</label>
                    <input type="text" class="form-control" id="editLocationName" name="locationName" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="capacity" class="form-label">Capacity</label>
                <input type="number" min="1" class="form-control" id="editCapacity" name="capacity" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between">
    <h1>Location Management</h1>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
        Add Location
    </button>
</div>
<div class="table-responsive mt-3">
    <table class="table table-striped table-bordered" id="locationTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Vehicle</th>
                <th>Location Name</th>
                <th>Capacity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

@endsection
@section('script')
<script>

    document.getElementById('addLocationModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('addLocationForm').reset();
    });

    function deleteLocation(id) {
        axios.delete(`{{ route('master.location.delete') }}`, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { id: id }
        })
        .then(response => {
            Swal.fire({
                icon: 'success',
                title: 'Location Deleted',
                text: 'Location has been deleted successfully!',
                timer: 2000,
                showConfirmButton: false
            });
            $('#locationTable').DataTable().ajax.reload();
        })
        .catch(error => {
            console.error('Error deleting location:', error);
            Swal.fire({
                icon: 'error',
                title: 'Something Went Wrong',
                text: error.message || 'Error deleting location',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    function editLocation(id, capacity, vehicle, name) {

        const vehicleSelect = document.getElementById('editVehicleId');

        for (let i = 0; i < vehicleSelect.options.length; i++) {
            if (vehicleSelect.options[i].innerText == vehicle) {
                vehicleSelect.selectedIndex = i;
                break;
            }
        }

        document.getElementById('editId').value = id;
        document.getElementById('editLocationName').value = name;
        document.getElementById('editCapacity').value = capacity;
    }

    $(document).ready(function () {

        const table = $('#locationTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.location.getlocations') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'vehicle', name: 'vehicle' },
                { data: 'location', name: 'location' },
                { data: 'capacity', name: 'capacity' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        document.getElementById('addLocationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const vehicleId = document.getElementById('vehicleId').value;
            const locationName = document.getElementById('locationName').value;

            axios.post('{{ route('master.location.store') }}', {
                vehicleId: vehicleId,
                locationName: locationName
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                if (response.status === 200) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addLocationModal'));

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Location added successfully!',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        document.getElementById('locationName').value = '';
                        table.ajax.reload();
                        modal.hide();
                    });
                }
            }).catch(function (error) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addLocationModal'));

                let message = 'Failed to add location. Please try again.';
                if (error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    document.getElementById('locationName').value = '';
                    modal.hide();
                });
            });
        });

        document.getElementById('editLocationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const vehicleId = document.getElementById('editVehicleId').value;
            const locationName = document.getElementById('editLocationName').value;
            const capacity = document.getElementById('editCapacity').value;

            axios.put('{{ route('master.location.update') }}', {
                id: id,
                vehicleId: vehicleId,
                locationName: locationName,
                capacity: capacity,
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                if (response.status === 200) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editLocationModal'));

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Location updated successfully!',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        table.ajax.reload();
                        modal.hide();
                    });
                }
                else if(response.status === 404){
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Location Not Found',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        table.ajax.reload();
                        modal.hide();
                    });
                }
            }).catch(function (error) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editLocationModal'));

                let message = 'Failed to update location. Please try again.';
                if (error.response && error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    modal.hide();
                });
            });
        });
    });
</script>
@endsection
