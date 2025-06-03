@extends('app.layout')
@section('content')
<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="addVehicleModalLabel">Add Vehicle</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addVehicleForm">
        <div class="modal-body">
            <div class="mb-3">
                <label for="vehicleName" class="form-label">Vehicle Name</label>
                <input type="text" class="form-control" id="vehicleName" name="vehicleName" required>
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
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="editVehicleModalLabel">Edit Vehicle</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editVehicleForm">
        <input type="hidden" id="editId" name="id">
        <div class="modal-body">
            <div class="mb-3">
                <label for="vehicleName" class="form-label">Vehicle Name</label>
                <input type="text" class="form-control" id="editVehicleName" name="vehicleName" required>
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
    <h1>Vehicle Master</h1>

    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
        Add Vehicle
    </button>
</div>
<div class="table-responsive mt-3">
    <table class="table table-striped table-bordered" id="vehicleTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Vehicle Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

@endsection
@section('script')
<script>

    document.getElementById('addVehicleModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('addVehicleForm').reset();
    });

    function deleteVehicle(id) {
        axios.delete(`{{ route('master.vehicle.delete') }}`, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: { id: id }
        })
        .then(response => {
            Swal.fire({
                icon: 'success',
                title: 'Vehicle Deleted',
                text: 'Vehicle has been deleted successfully!',
                timer: 2000,
                showConfirmButton: false
            });
            $('#vehicleTable').DataTable().ajax.reload();
        })
        .catch(error => {
            console.error('Error deleting vehicle:', error);
            Swal.fire({
                icon: 'error',
                title: 'Something Went Wrong',
                text: error.message || 'Error deleting vehicle',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    function editVehicle(id, name) {
        document.getElementById('editId').value = id;
        document.getElementById('editVehicleName').value = name;
    }

    $(document).ready(function () {

        const table = $('#vehicleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.vehicle.getvehicles') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        document.getElementById('addVehicleForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const vehicleName = document.getElementById('vehicleName').value;

            axios.post('{{ route('master.vehicle.store') }}', {
                vehicleName: vehicleName
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                if (response.status === 200) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addVehicleModal'));

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Vehicle added successfully!',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        document.getElementById('vehicleName').value = '';
                        table.ajax.reload();
                        modal.hide();
                    });
                }
            }).catch(function (error) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addVehicleModal'));

                let message = 'Failed to add vehicle. Please try again.';
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
                    document.getElementById('vehicleName').value = '';
                    modal.hide();
                });
            });
        });

        document.getElementById('editVehicleForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const vehicleName = document.getElementById('editVehicleName').value;

            axios.put('{{ route('master.vehicle.update') }}', {
                id: id,
                vehicleName: vehicleName
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(function (response) {
                if (response.status === 200) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editVehicleModal'));

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Vehicle updated successfully!',
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
                        text: 'Vehicle Not Found',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        table.ajax.reload();
                        modal.hide();
                    });
                }
            }).catch(function (error) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editVehicleModal'));

                let message = 'Failed to update vehicle. Please try again.';
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
