<header class="pb-3 mb-4 border-bottom">

    {{--
    Navbar utama:
    - `navbar-expand-lg`: Hanya akan expand (tampil horizontal) di layar besar.
    - `bg-light` dan `rounded`: Memberi tampilan visual dasar.
    --}}
    <nav class="navbar navbar-expand-lg bg-light rounded" aria-label="Admin navigation">
        <div class="container-fluid">

            {{-- Brand/Judul Utama --}}
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i>
                Dashboard Admin
            </a>

            {{-- Tombol Hamburger (Toggle) untuk mobile --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent"
                aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Konten Navbar yang bisa collaps --}}
            <div class="collapse navbar-collapse" id="adminNavbarContent">

                {{--
                Daftar Navigasi Utama:
                - `me-auto`: Mendorong item lain (logout) ke kanan.
                - `nav-pills`: Memberi tampilan tombol/pil pada item aktif.
                --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-pills">

                    {{-- Item: Aktivitas Driver --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-person-workspace"></i> Aktivitas Driver
                        </a>
                    </li>

                    {{-- Item: Laporan Darurat --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.laporan_darurat') }}"
                            class="nav-link {{ request()->routeIs('admin.laporan_darurat') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle"></i> Laporan Darurat
                        </a>
                    </li>

                    {{-- Item: Riwayat Unit --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.riwayat_unit') }}"
                            class="nav-link {{ request()->routeIs('admin.riwayat_unit') ? 'active' : '' }}">
                            <i class="bi bi-card-checklist"></i> Riwayat Unit
                        </a>
                    </li>

                    {{-- Item: Kelola Driver --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.driver.index') }}"
                            class="nav-link {{ request()->routeIs('admin.driver.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i> Kelola Driver
                        </a>
                    </li>

                    {{-- Item: Kelola Pengguna --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.pengguna.index') }}"
                            class="nav-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Kelola Pengguna
                        </a>
                    </li>

                    {{-- Item: Daftar Aset --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.daftar_aset') }}"
                            class="nav-link {{ request()->routeIs('admin.daftar_aset', 'admin.aset.*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i> Daftar Aset
                        </a>
                    </li>

                    {{-- Item: Dropdown Rekap --}}
                    <li class="nav-item dropdown">
                        {{--
                        Logika 'active' untuk dropdown:
                        Aktif jika salah satu route di dalamnya (harian ATAU bulanan) aktif.
                        --}}
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.rekap_harian', 'admin.rekap_bulanan') ? 'active' : '' }}"
                            href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-journal-check"></i> Rekap
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('admin.rekap_harian') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_harian') }}">Rekap Harian
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('admin.rekap_bulanan') ? 'active' : '' }}"
                                    href="{{ route('admin.rekap_bulanan') }}">Rekap Bulanan
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                {{--
                Form Logout:
                - Tidak perlu `d-flex` jika hanya ada satu tombol.
                - `ms-auto` di `<ul>` sebelumnya sudah mendorong ini ke kanan.
                    --}}
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>

            </div>
        </div>
    </nav>
</header>