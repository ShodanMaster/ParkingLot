@extends('app.layout')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white fs-4">
            Scan Out
        </div>
        <form>
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
        const code = codeInput.value;
        console.log("Code input:", code);

        axios.post('{{ route('transaction.scan.scanout') }}', {
            code: code
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log("Response:", response.data);
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});

    </script>
@endsection
