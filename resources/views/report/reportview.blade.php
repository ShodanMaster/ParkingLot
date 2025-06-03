@extends('app.layout')
@section('content')
    <div class="d-flex justify-content-end mb-1">
        <button type="button" class="btn btn-sm btn-danger" id="pdf">PDF</button>
        <button type="button" class="btn btn-sm btn-success mx-2" id="excel">Excel</button>
    </div>
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
            const filterParams = {
                from_date: '{{$fromDate}}',
                to_date: '{{$toDate}}',
                qrcode: '{{$qrcode}}',
                vehicle_number: '{{$vehicleNumber}}',
                location: '{{$location}}',
                status: '{{$status}}',
                inTimeFrom: '{{$inTimeFrom}}',
                inTimeTo: '{{$inTimeTo}}',
                outTimeFrom: '{{$outTimeFrom}}',
                outTimeTo: '{{$outTimeTo}}',
                _token: '{{ csrf_token() }}'
            };

            const table = $('#reportTable').DataTable({
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
                    data: function (d) {
                        return Object.assign(d, filterParams);
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'vehicle_number', name: 'vehicle_number' },
                    { data: 'qrcode', name: 'qrcode' },
                    { data: 'location', name: 'location' },
                    { data: 'in_time', name: 'in_time' },
                    { data: 'out_time', name: 'out_time' },
                    { data: 'status', name: 'status' },
                ]
            });

            function submitReportForm(actionValue) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("report.reportview") }}';

                const allParams = { ...filterParams, action: actionValue };

                for (const key in allParams) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = allParams[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
                form.remove();
            }

            document.getElementById('pdf').addEventListener('click', function (e) {
                e.preventDefault();
                submitReportForm('3');
            });

            document.getElementById('excel').addEventListener('click', function (e) {
                e.preventDefault();
                submitReportForm('2');
            });
        });
    </script>

@endsection
