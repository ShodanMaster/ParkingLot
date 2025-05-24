@extends('app.layout')
@section('content')
<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="addVehicleModalLabel">Modal title</h1>
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
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between">
    <h1>Vehicle Management</h1>

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

    $(document).ready(function() {
        $('#vehicleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('master.vehicle.getvehicles') }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action' }
            ]
        });
    });

    document.getElementById('addVehicleForm').addEventListener('submit', function(e){
        e.preventDefault();
        const vehicleName = document.getElementById('vehicleName').value;

        axios.post('{{route('master.vehicle.store')}}', {
            vehicleName: vehicleName
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(function(response) {
            console.log(response);
            if(response.status === 200){
                var modal = bootstrap.Modal.getInstance(document.getElementById('addVehicleModal'));
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Vehicle added successfully!',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    document.getElementById('vehicleName').value = '';
                    modal.hide();
                });

            }

        }).catch(function(error) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('addVehicleModal'));

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
</script>

@endsection
