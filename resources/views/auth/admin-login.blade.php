<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Hamada</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .login-header {
            background-color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 2rem 2rem 1rem;
            text-align: center;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn-primary {
            padding: 0.6rem;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <div class="card login-card">

                    {{-- Header Kartu Login --}}
                    <div class="login-header">

                        <h4 class="fw-bold text-dark">Login Admin Hamada</h4>
                        <p class="text-muted small">Masuk untuk mengelola dashboard</p>
                    </div>

                    {{-- Body Kartu Login --}}
                    <div class="card-body login-body pt-0">

                        {{-- Menampilkan Pesan Error jika Login Gagal --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                <small>
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    {{ $errors->first() }}
                                </small>
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.login.submit') }}" method="POST">
                            @csrf

                            {{-- Input Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label text-secondary small fw-bold">ALAMAT EMAIL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-secondary">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email"
                                        class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" placeholder="nama@contoh.com"
                                        required autofocus>
                                </div>
                            </div>

                            {{-- Input Password --}}
                            <div class="mb-4">
                                <label for="password" class="form-label text-secondary small fw-bold">PASSWORD</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-secondary">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Masukkan password" required>
                                </div>
                            </div>

                            {{-- Checkbox Ingat Saya --}}
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label small text-secondary" for="remember">Ingat saya di
                                    perangkat ini</label>
                            </div>

                            {{-- Tombol Login --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                                </button>
                            </div>

                        </form>
                    </div>

                    {{-- Footer Kartu (Opsional) --}}
                    <div class="card-footer bg-light text-center py-3 border-0"
                        style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <small class="text-muted">&copy; {{ date('Y') }} Hamada Transport</small>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>