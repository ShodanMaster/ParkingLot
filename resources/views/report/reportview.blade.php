@extends('app.layout')
@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white fs-4">
            Report
        </div>
        <form>
            <div class="card-body table-responsive">
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle Number</th>
                            <th>Qr Code</th>
                            <th>Location</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {

            var table = $('#reportTable').DataTable({
                language: { search: "", searchPlaceholder: "Search" },
                pageLength: 10,
                lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                autoWidth: false,
                processing: true,
                serverSide: true,
                columnDefs: [
                    { className: "text-center", targets: [0] },
                    { width: 50, targets: [0] },
                    { orderable: false, targets: [0, 6] }
                ],
                ajax: {
                    url: '{{ route('report.getreports') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(d) {
                        d.fromDate = '{{ $fromDate }}';
                        d.toDate = '{{ $toDate }}';
                        d.qrcode = '{{ $qrcode }}';
                        d.vehicleNumber = '{{ $vehicleNumber }}';
                        d.location = '{{ $location }}';
                        d.status = '{{ $status }}';
                        d.inTimeFrom = '{{ $inTimeFrom }}';
                        d.inTimeTo = '{{ $inTimeTo }}';
                        d.outTimeFrom = '{{ $outTimeFrom }}';
                        d.outTimeTo = '{{ $outTimeTo }}';
                    },
                },

                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'vehicle_number', name: 'vehicle_number' },
                    { data: 'qrcode', name: 'qrcode' },
                    { data: 'location', name: 'location' },
                    { data: 'in_time', name: 'in_time' },
                    { data: 'out_time', name: 'out_time'},
                    { data: 'status', name: 'status' },
                ]
            });

        });
    </script>
@endsection
