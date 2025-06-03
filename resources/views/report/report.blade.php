@extends('app.layout')
@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white fs-4">
            Report Form
        </div>
        <form action="{{route('report.reportview')}}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" name="from_date" id="from_date">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" name="to_date" id="to_date">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_number" class="form-label">Vehicle Number</label>
                            <input type="text" class="form-control" name="vehicle_number" id="vehicle_number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="qrcode" class="form-label">QR Code</label>
                            <input type="text" class="form-control" name="qrcode" id="qrcode">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="locaiton" class="form-label">Location</label>
                            <select class="form-control" name="location" id="location">
                                <option value="" selected disabled>Select Location</option>

                                @forelse ($locations as $location)
                                    <option value="{{$location->id}}">{{$location->name}}</option>
                                @empty
                                    <option value="" selected disabled>No Locations</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="" disabled selected>Select Status</option>
                            <option value="IN">IN</option>
                            <option value="OUT">OUT</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="inTimeFrom" class="form-label">In-Time From</label>
                                <input type="time" class="form-control" name="inTimeFrom" id="inTimeFrom">
                            </div>
                            <div class="col-md-6">
                                <label for="inTimeTo" class="form-label">In-Time To</label>
                                <input type="time" class="form-control" name="inTimeTo" id="inTimeTo">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="outTimeFrom" class="form-label">Out-Time From</label>
                                <input type="time" class="form-control" name="outTimeFrom" id="outTimeFrom">
                            </div>
                            <div class="col-md-6">
                                <label for="outTimeTo" class="form-label">Out-Time To</label>
                                <input type="time" class="form-control" name="outTimeTo" id="outTimeTo">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="submit" name="action" class="btn btn-sm btn-danger" value="3">PDF</button>
                <button type="submit" name="action" class="btn btn-sm btn-success mx-2" value="2">Excel</button>
                <button type="submit" name="action" class="btn btn-sm btn-primary" value="1">Show</button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qrInput = document.getElementById('qrcode');

            const fieldsToToggle = [
                'from_date',
                'to_date',
                'vehicle_number',
                'location',
                'status',
                'inTimeFrom',
                'inTimeTo',
                'outTimeFrom',
                'outTimeTo'
            ];

            qrInput.addEventListener('input', function () {
                const isQRFilled = qrInput.value.trim() !== '';

                fieldsToToggle.forEach(id => {
                    const field = document.getElementById(id);
                    if (field) {
                        field.value = '';
                        field.disabled = isQRFilled;
                    }
                });
            });
        });
    </script>
@endsection
