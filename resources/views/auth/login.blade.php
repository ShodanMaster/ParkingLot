<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ParkingLot|Login</title>
    <link rel="stylesheet" href="{{ asset('asset/bootstrap/bootstrap.min.css') }}">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-center text-white fs-4"> Login</div>
            <form id="loginForm">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('asset/js/axios/axios.min.js') }}"></script>
    <script src="{{ asset('asset/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('asset/js/sweetalert/sweetalert.min.js') }}"></script>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;

        axios.post('{{ route('logingin') }}', {
            username: username,
            password: password,
            remember: rememberMe
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.data.status === 200) {
                Swal.fire({
                    title: 'Success',
                    text: response.data.message || 'Login successful',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => {
                    
                    window.location.href = response.data.route || '/dashboard';
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.data.message || 'Login failed',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            }
        })
        .catch(error => {
            let msg = 'An error occurred during login';
            if (error.response && error.response.data && error.response.data.message) {
                msg = error.response.data.message;
            }
            Swal.fire({
                title: 'Error',
                text: msg,
                icon: 'error',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true
            });
            console.error('Login error:', error);
        });
    });
    </script>

</body>
</html>
