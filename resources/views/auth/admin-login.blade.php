<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Hamada Transport</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Fonts - Menggunakan Inter untuk kesan modern --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Palette warna disesuaikan agar tetap corporate tapi soft */
            --primary-red: #dc2626;
            /* Warna H */
            --primary-blue: #1e40af;
            /* Warna amada & logistik */
            --neutral-bg: #f9fafb;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            /* Gambar background asli Anda */
            background-image: url("{{ asset('images/Cover-web-HL.bk.png') }}");
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* ── Efek Background (Overlay Gelap + Blur Halus) ── */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            /* Overlay warna navy gelap yang transparan */
            background-color: rgba(15, 23, 42, 0.65);
            /* Efek blur agar background tetap terlihat tapi clean */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            /* Memastikan form berada di atas efek background */
            width: 100%;
            max-width: 420px;
        }

        /* ── Card - Tetap Putih, Clean, & Elegan ── */
        .login-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            /* Bayangan lembut agar terlihat melayang di atas background */
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        /* ── Header ── */
        .login-header {
            background: #ffffff;
            padding: 2.5rem 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 0.5rem;
        }

        .logo-icon {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .logo-text {
            text-align: center;
        }

        /* Styling Spesifik Nama Brand */
        .logo-text .brand {
            font-size: 1.75rem;
            font-weight: 800;
            /* Ketebalan tebal (Bold) untuk semua teks */
            letter-spacing: -0.5px;
            line-height: 1;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* H Merah */
        .brand-h {
            color: var(--primary-red);
        }

        /* amada Biru */
        .brand-amada {
            color: var(--primary-blue);
        }

        /* LOGISTIK Biru - Sekarang disamakan dengan amada (Font-weight 400 dihapus) */
        .brand-logistik {
            color: var(--primary-blue);
        }

        .logo-text .tagline {
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* ── Body ── */
        .login-body {
            padding: 2rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border-radius: 8px;
            background-color: #ffffff;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
            background-color: #ffffff;
            outline: none;
        }

        .form-control::placeholder {
            color: #d1d5db;
        }

        .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .form-check-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* ── Button ── */
        .btn-login {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
            color: white;
        }

        /* ── Alert ── */
        .alert {
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
        }

        /* ── Footer ── */
        .login-footer {
            background: var(--neutral-bg);
            padding: 1rem 2rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .login-footer small {
            color: var(--text-muted);
            font-size: 0.825rem;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="card login-card">

            {{-- Header --}}
            <div class="login-header">
                <div class="logo-wrap">
                    <div class="logo-icon">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div class="logo-text">
                        <div class="brand">
                            <span class="brand-h">H</span><span class="brand-amada">amada</span>
                            <span class="brand-logistik">Logistik</span>
                        </div>
                        <div class="tagline">Transport &amp; Logistics</div>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="login-body">

                <div class="section-title">Silahkan Login</div>

                {{-- Error Alert --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Masukkan password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    {{-- Login Button --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login">
                            Masuk ke Dashboard
                        </button>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="login-footer">
                <small>
                    &copy; {{ date('Y') }} PT Hamada Logistik Internasional.
                </small>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
