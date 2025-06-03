@extends('app.layout')
@section('content')
    <div class="container mt-5">
        <div class="row">
            @foreach ($locations as $location)
                <div class="col-md-3 mb-4">
                    <div class="card shadow-lg border-light rounded hover-shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="font-weight-bold">
                                {{$location->name}}
                            </div>
                            <div class="text-muted">
                                {{ count($location->allocates->filter(function($allocate) { return is_null($allocate->out_time); })) }} / {{$location->slot}}
                            </div>
                        </div>
                        <div class="card-body text-center">
                            <!-- Canvas for Pie Chart -->
                            <canvas id="locationChart{{$location->id}}" width="200" height="200"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script>
        // Reusable function to create the pie chart
        function createPieChart(canvasId, allocated, available) {
            var ctx = document.getElementById(canvasId).getContext('2d');

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Allocated', 'Available'],
                    datasets: [{
                        label: 'Occupancy',
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
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Loop through the locations and create the pie chart for each
        @foreach ($locations as $location)
            document.addEventListener('DOMContentLoaded', function() {
                createPieChart('locationChart{{$location->id}}', {{ count($location->allocates->filter(function($allocate) { return is_null($allocate->out_time); })) }} , {{$location->slot - count($location->allocates)}});
            });
        @endforeach
    </script>
@endpush
