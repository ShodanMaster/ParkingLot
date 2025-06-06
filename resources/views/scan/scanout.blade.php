@extends('app.master')
@section('mastercontent')
    <div class="card">
        <div class="card-header bg-primary text-center text-white fs-4">
            Scan Out
        </div>
        <form>
            <div class="card-body">
                <div class="form-group">
                    <label for="codee" class="form-label">Code:</label>
                    <input type="text" class="form-control" name="code" id="code" autocomplete="off">
                </div>
            </div>
        </form>
    </div>

     <script>
        document.addEventListener("DOMContentLoaded", function () {
            const codeInput = document.getElementById("code");

            codeInput.addEventListener("input", function () {
                const code = codeInput.value.trim();

                if (code !== '') {
                    axios.post('{{ route('scan.scanningout') }}', {
                        code: code
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {

                        codeInput.value = '';

                        if (response.data.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.data.message || 'Scanned Out',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.data.message || 'Something went wrong!',
                            });
                        }
                    })
                    .catch(error => {
                        codeInput.value = '';

                        let message = 'An unexpected error occurred.';
                        if (error.response && error.response.data && error.response.data.message) {
                            message = error.response.data.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: message,
                        });
                    });
                }
            });
        });
        </script>

@endsection
