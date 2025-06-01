@extends('app.layout')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white fs-4">
            Scan Out
        </div>
        <form>
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="code" class="form-label">Code:</label>
                    <input type="text" class="form-control" name="code" id="code" autocomplete="off">
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const codeInput = document.getElementById("code");

            codeInput.addEventListener("input", function () {
                axios.post('{{route('transaction.scan.scanout')}}', {
                    header: "{{csrf_token()}}"
                },
                {
                    
                })
            });
        });
    </script>
@endsection
