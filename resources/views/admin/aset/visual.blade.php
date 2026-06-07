@extends('admin.layouts.app')

@section('title', 'Visual Check - ' . $vehicle->plate_number)

@section('content')
    <div class="container-fluid p-0">

        {{-- HEADER HALAMAN --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Diagnostic Dashboard</h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-primary fs-6">{{ $vehicle->plate_number }}</span>
                    <span class="badge bg-secondary fs-6">
                        <i class="bi bi-truck me-1"></i>{{ $vehicle->type }}
                    </span>
                    <span class="text-muted small border-start ps-2 ms-1">
                        Update: {{ $lastLog ? \Carbon\Carbon::parse($lastLog->time_out)->format('d/m/Y H:i') : '-' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('admin.maintenance.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- DISCLAIMER --}}
        <div class="alert alert-light border mb-4">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle text-primary fs-4 me-3 mt-1"></i>
                <div>
                    <strong>Tentang Diagnostic Dashboard</strong>
                    <p class="mb-0 small text-muted">
                        Model 3D ini adalah representasi generik untuk visualisasi sistem diagnostik.
                        Sistem menggabungkan data operasional (checklist driver) dengan data prediktif (interval
                        maintenance)
                        untuk memberikan analisis yang lebih akurat. Fokus pada status komponen di panel kanan.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- KOLOM KIRI: 3D VIEW --}}
            <div class="col-lg-8">
                <div class="visual-card">
                    <div id="vehicle-3d"></div>

                    {{-- Loading Screen --}}
                    <div class="loading-overlay" id="loader">
                        <div class="spinner"></div>
                        <small>Memuat Model 3D...</small>
                    </div>

                    {{-- Controls --}}
                    <div class="controls-3d">
                        <button class="btn-3d" onclick="resetCamera()" title="Reset Posisi"><i
                                class="bi bi-arrow-counterclockwise"></i></button>
                        <button class="btn-3d" onclick="toggleAutoRotate()" title="Putar Otomatis"><i
                                class="bi bi-play-fill"></i></button>
                        <button class="btn-3d" onclick="toggleWireframe()" title="Mode Garis"><i
                                class="bi bi-grid-3x3"></i></button>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PANEL DIAGNOSTIK --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-cpu text-primary me-2"></i>System Diagnostics
                        </h5>
                        <small class="text-muted">Hybrid Analysis: Operasional + Prediktif</small>
                    </div>
                    <div class="card-body">
                        <div class="vstack gap-3">

                            {{-- 1. KONDISI BAN --}}
                            <div class="diagnostic-item p-3 rounded border 
                                    {{ $finalStatus['ban'] == 'danger' ? 'border-danger bg-danger-subtle' : ($finalStatus['ban'] == 'warning' ? 'border-warning bg-warning-subtle' : '') }}"
                                onmouseover="highlightPart('wheels')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-3">
                                        <i
                                            class="bi bi-vinyl fs-4 mt-1
                                                {{ $finalStatus['ban'] == 'danger' ? 'text-danger' : ($finalStatus['ban'] == 'warning' ? 'text-warning' : 'text-success') }}">
                                        </i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Kondisi Ban</div>
                                            <div class="small text-muted mb-2">Tekanan & Fisik</div>

                                            {{-- Status Badges --}}
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <span
                                                    class="badge badge-sm {{ $operationalStatus['ban'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                                    Driver:
                                                    {{ $operationalStatus['ban'] == 'danger' ? 'Bermasalah' : 'Aman' }}
                                                </span>
                                                @if($predictiveStatus['ban'] != 'unknown')
                                                    <span
                                                        class="badge badge-sm {{ $predictiveStatus['ban'] == 'danger' ? 'bg-warning text-dark' : 'bg-info' }}">
                                                        Interval: {{ $predictiveStatus['ban'] == 'danger' ? 'Due' : 'OK' }}
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Detail Info --}}
                                            @if($operationalStatus['ban'] == 'danger' || $predictiveStatus['ban'] != 'safe')
                                                <div class="small text-muted">
                                                    @if($operationalStatus['ban'] == 'danger')
                                                        <div><i class="bi bi-exclamation-triangle text-danger"></i> Driver
                                                            melaporkan masalah</div>
                                                    @endif
                                                    @if($predictiveStatus['ban'] == 'danger' && isset($detailInfo['ban']))
                                                        @foreach($detailInfo['ban'] as $detail)
                                                            <div><i class="bi bi-clock-history text-warning"></i> {{ $detail['name'] }}:
                                                                Sisa {{ number_format($detail['km_remaining']) }} KM</div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill 
                                            {{ $finalStatus['ban'] == 'danger' ? 'bg-danger' : ($finalStatus['ban'] == 'warning' ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $finalStatus['ban'] == 'danger' ? 'PERIKSA' : ($finalStatus['ban'] == 'warning' ? 'WARNING' : 'OK') }}
                                    </span>
                                </div>
                            </div>

                            {{-- 2. SISTEM REM --}}
                            <div class="diagnostic-item p-3 rounded border 
                                    {{ $finalStatus['rem'] == 'danger' ? 'border-danger bg-danger-subtle' : ($finalStatus['rem'] == 'warning' ? 'border-warning bg-warning-subtle' : '') }}"
                                onmouseover="highlightPart('rem')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-3">
                                        <i
                                            class="bi bi-sign-stop-fill fs-4 mt-1
                                                {{ $finalStatus['rem'] == 'danger' ? 'text-danger' : ($finalStatus['rem'] == 'warning' ? 'text-warning' : 'text-success') }}">
                                        </i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Sistem Rem</div>
                                            <div class="small text-muted mb-2">Kampas & Minyak</div>

                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <span
                                                    class="badge badge-sm {{ $operationalStatus['rem'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                                    Driver:
                                                    {{ $operationalStatus['rem'] == 'danger' ? 'Bermasalah' : 'Aman' }}
                                                </span>
                                                @if($predictiveStatus['rem'] != 'unknown')
                                                    <span
                                                        class="badge badge-sm {{ $predictiveStatus['rem'] == 'danger' ? 'bg-warning text-dark' : 'bg-info' }}">
                                                        Interval: {{ $predictiveStatus['rem'] == 'danger' ? 'Due' : 'OK' }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($operationalStatus['rem'] == 'danger' || $predictiveStatus['rem'] != 'safe')
                                                <div class="small text-muted">
                                                    @if($operationalStatus['rem'] == 'danger')
                                                        <div><i class="bi bi-exclamation-triangle text-danger"></i> Driver
                                                            melaporkan masalah</div>
                                                    @endif
                                                    @if($predictiveStatus['rem'] == 'danger' && isset($detailInfo['rem']))
                                                        @foreach($detailInfo['rem'] as $detail)
                                                            <div><i class="bi bi-clock-history text-warning"></i> {{ $detail['name'] }}:
                                                                Sisa {{ number_format($detail['km_remaining']) }} KM</div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill 
                                            {{ $finalStatus['rem'] == 'danger' ? 'bg-danger' : ($finalStatus['rem'] == 'warning' ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $finalStatus['rem'] == 'danger' ? 'BAHAYA' : ($finalStatus['rem'] == 'warning' ? 'WARNING' : 'OK') }}
                                    </span>
                                </div>
                            </div>

                            {{-- 3. KELISTRIKAN --}}
                            <div class="diagnostic-item p-3 rounded border 
                                    {{ $finalStatus['lampu'] == 'danger' ? 'border-danger bg-danger-subtle' : ($finalStatus['lampu'] == 'warning' ? 'border-warning bg-warning-subtle' : '') }}"
                                onmouseover="highlightPart('lampu')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-3">
                                        <i
                                            class="bi bi-lightbulb-fill fs-4 mt-1
                                                {{ $finalStatus['lampu'] == 'danger' ? 'text-danger' : ($finalStatus['lampu'] == 'warning' ? 'text-warning' : 'text-success') }}">
                                        </i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Kelistrikan</div>
                                            <div class="small text-muted mb-2">Lampu & Sein</div>

                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <span
                                                    class="badge badge-sm {{ $operationalStatus['lampu'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                                    Driver:
                                                    {{ $operationalStatus['lampu'] == 'danger' ? 'Bermasalah' : 'Aman' }}
                                                </span>
                                                @if($predictiveStatus['lampu'] != 'unknown')
                                                    <span
                                                        class="badge badge-sm {{ $predictiveStatus['lampu'] == 'danger' ? 'bg-warning text-dark' : 'bg-info' }}">
                                                        Interval: {{ $predictiveStatus['lampu'] == 'danger' ? 'Due' : 'OK' }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($operationalStatus['lampu'] == 'danger' || $predictiveStatus['lampu'] != 'safe')
                                                <div class="small text-muted">
                                                    @if($operationalStatus['lampu'] == 'danger')
                                                        <div><i class="bi bi-exclamation-triangle text-danger"></i> Driver
                                                            melaporkan masalah</div>
                                                    @endif
                                                    @if($predictiveStatus['lampu'] == 'danger' && isset($detailInfo['lampu']))
                                                        @foreach($detailInfo['lampu'] as $detail)
                                                            <div><i class="bi bi-clock-history text-warning"></i> {{ $detail['name'] }}:
                                                                Perlu penggantian</div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill 
                                            {{ $finalStatus['lampu'] == 'danger' ? 'bg-danger' : ($finalStatus['lampu'] == 'warning' ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $finalStatus['lampu'] == 'danger' ? 'RUSAK' : ($finalStatus['lampu'] == 'warning' ? 'WARNING' : 'OK') }}
                                    </span>
                                </div>
                            </div>

                            {{-- 4. MESIN --}}
                            <div class="diagnostic-item p-3 rounded border 
                                    {{ $finalStatus['mesin'] == 'danger' ? 'border-danger bg-danger-subtle' : ($finalStatus['mesin'] == 'warning' ? 'border-warning bg-warning-subtle' : '') }}"
                                onmouseover="highlightPart('cabin')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-3">
                                        <i
                                            class="bi bi-gear-wide-connected fs-4 mt-1
                                                {{ $finalStatus['mesin'] == 'danger' ? 'text-danger' : ($finalStatus['mesin'] == 'warning' ? 'text-warning' : 'text-success') }}">
                                        </i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">Mesin & Oli</div>
                                            <div class="small text-muted mb-2">Interval Servis</div>

                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                @if($predictiveStatus['mesin'] != 'unknown')
                                                    <span
                                                        class="badge badge-sm {{ $predictiveStatus['mesin'] == 'danger' ? 'bg-warning text-dark' : 'bg-info' }}">
                                                        Interval: {{ $predictiveStatus['mesin'] == 'danger' ? 'Due' : 'OK' }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($predictiveStatus['mesin'] != 'safe')
                                                <div class="small text-muted">
                                                    @if($predictiveStatus['mesin'] == 'danger' && isset($detailInfo['mesin']))
                                                        @foreach($detailInfo['mesin'] as $detail)
                                                            <div><i class="bi bi-clock-history text-warning"></i> {{ $detail['name'] }}:
                                                                Sisa {{ number_format($detail['km_remaining']) }} KM</div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill 
                                            {{ $finalStatus['mesin'] == 'danger' ? 'bg-danger' : ($finalStatus['mesin'] == 'warning' ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $finalStatus['mesin'] == 'danger' ? 'DUE' : ($finalStatus['mesin'] == 'warning' ? 'WARNING' : 'PRIMA') }}
                                    </span>
                                </div>
                            </div>

                        </div>

                        {{-- CATATAN DRIVER --}}
                        <div class="mt-4 pt-3 border-top">
                            <label class="small text-muted fw-bold mb-2 text-uppercase">Catatan Driver:</label>
                            <div class="p-3 bg-light rounded border">
                                <p class="mb-0 fst-italic small text-dark">
                                    @if ($lastLog && $lastLog->catatan)
                                        "{{ $lastLog->catatan }}"
                                    @else
                                        - Tidak ada catatan khusus -
                                    @endif
                                </p>
                            </div>
                            <div class="text-end mt-2 small text-muted mb-3">
                                Driver: <strong>{{ $lastLog->driver->full_name ?? '-' }}</strong>
                            </div>

                            {{-- TOMBOL AKSI --}}
                            <label class="small text-muted fw-bold mb-2 text-uppercase">Tindakan Lanjutan:</label>
                            <div class="d-grid gap-2">
                                @if(in_array('danger', $operationalStatus))
                                <form action="{{ route('admin.aset.resolveIssue', $vehicle->id) }}" method="POST" class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Tandai bahwa keluhan fisik kendaraan dari driver telah diperbaiki?')">
                                        <i class="bi bi-check-circle-fill me-2"></i>Tandai Keluhan Driver Diperbaiki
                                    </button>
                                </form>
                                @endif
                                
                                <a href="{{ route('admin.aset.riwayat', $vehicle->id) }}" class="btn btn-primary">
                                    <i class="bi bi-tools me-2"></i>Buka Buku Riwayat / Buat Jadwal Servis
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STYLES --}}
    <style>
        .visual-card {
            background: #1e272e;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            height: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        #vehicle-3d {
            width: 100%;
            height: 100%;
            outline: none;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #1e272e;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            color: white;
            flex-direction: column;
            transition: opacity 0.5s ease;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: #f39c12;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .controls-3d {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            padding: 8px 16px;
            border-radius: 50px;
            display: flex;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-3d {
            background: transparent;
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            cursor: pointer;
        }

        .btn-3d:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .diagnostic-item {
            transition: 0.2s;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .diagnostic-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
    </style>

    {{-- SCRIPTS --}}
    @push('scripts')
        {{-- FIX: Gunakan satu CDN yang sama (unpkg) agar THREE.OrbitControls terdaftar dengan benar --}}
        <script src="https://unpkg.com/three@0.128.0/build/three.min.js"></script>
        <script src="https://unpkg.com/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

        <script>
            // --- VARIABLES ---
            let scene, camera, renderer, controls;
            let truckGroup;
            let parts = {};
            let isAutoRotate = false;
            let damagedParts = [];
            let pulseTime = 0;

            // Data Status dari Controller (Hybrid Analysis)
            const vehicleStatus = {
                ban: "{{ $finalStatus['ban'] }}",
                rem: "{{ $finalStatus['rem'] }}",
                lampu: "{{ $finalStatus['lampu'] }}",
                mesin: "{{ $finalStatus['mesin'] }}"
            };

            // Vehicle Type
            const vehicleType = "{{ $vehicle->type }}";

            function init() {
                const container = document.getElementById('vehicle-3d');

                // 1. Scene & Fog
                scene = new THREE.Scene();
                scene.background = new THREE.Color(0x1e272e);
                scene.fog = new THREE.Fog(0x1e272e, 10, 60);

                // 2. Camera
                camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
                camera.position.set(8, 5, 8);

                // 3. Renderer
                renderer = new THREE.WebGLRenderer({
                    antialias: true
                });
                renderer.setSize(container.clientWidth, container.clientHeight);
                renderer.shadowMap.enabled = true;
                renderer.shadowMap.type = THREE.PCFSoftShadowMap;
                container.appendChild(renderer.domElement);

                // 4. Lights
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
                scene.add(ambientLight);

                const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
                dirLight.position.set(10, 20, 10);
                dirLight.castShadow = true;
                dirLight.shadow.mapSize.width = 2048;
                dirLight.shadow.mapSize.height = 2048;
                scene.add(dirLight);

                // 5. Controls
                controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.05;
                controls.maxPolarAngle = Math.PI / 2 - 0.05; // Prevent going below ground
                controls.minDistance = 5;
                controls.maxDistance = 25;

                // 6. Content
                createFloor();
                createTruckModel();

                // 7. Event
                window.addEventListener('resize', onWindowResize);

                // Hide Loader & Start Effects
                setTimeout(() => {
                    const loader = document.getElementById('loader');
                    if (loader) {
                        loader.style.opacity = '0';
                        setTimeout(() => loader.style.display = 'none', 500);
                    }
                    applyDamageEffects();
                }, 800);

                animate();
            }

            function createFloor() {
                const floorGeo = new THREE.PlaneGeometry(60, 60);
                const floorMat = new THREE.MeshStandardMaterial({
                    color: 0x2c3e50,
                    roughness: 0.8
                });
                const floor = new THREE.Mesh(floorGeo, floorMat);
                floor.rotation.x = -Math.PI / 2;
                floor.receiveShadow = true;
                scene.add(floor);

                const grid = new THREE.GridHelper(60, 20, 0x555555, 0x333333);
                scene.add(grid);
            }

            function createTruckModel() {
                truckGroup = new THREE.Group();

                // --- Materials (Isuzu ELF style: white body) ---
                const matCabinWhite = new THREE.MeshStandardMaterial({
                    color: 0xf0f0f0, roughness: 0.35, metalness: 0.05
                });
                const matChassis = new THREE.MeshStandardMaterial({
                    color: 0x1a1a1a, roughness: 0.9
                });
                const matFrame = new THREE.MeshStandardMaterial({
                    color: 0x2a2a2a, roughness: 0.8, metalness: 0.3
                });
                const matBox = new THREE.MeshStandardMaterial({
                    color: 0xe8e8e8, roughness: 0.45
                });
                const matBoxTrim = new THREE.MeshStandardMaterial({
                    color: 0xcccccc, roughness: 0.5, metalness: 0.15
                });
                const matGlass = new THREE.MeshStandardMaterial({
                    color: 0x1a3a5c, transparent: true, opacity: 0.55, roughness: 0.05, metalness: 0.1
                });
                const matTire = new THREE.MeshStandardMaterial({
                    color: 0x1a1a1a, roughness: 0.95
                });
                const matRim = new THREE.MeshStandardMaterial({
                    color: 0x888888, metalness: 0.6, roughness: 0.3
                });
                const matHeadlight = new THREE.MeshStandardMaterial({
                    color: 0xffffff, emissive: 0xffffdd, emissiveIntensity: 0.4, roughness: 0.1
                });
                const matTaillight = new THREE.MeshStandardMaterial({
                    color: 0xcc0000, emissive: 0x880000, emissiveIntensity: 0.3
                });
                const matIndicator = new THREE.MeshStandardMaterial({
                    color: 0xff8800, emissive: 0xcc6600, emissiveIntensity: 0.3
                });
                const matGrille = new THREE.MeshStandardMaterial({
                    color: 0x333333, roughness: 0.7
                });
                const matBumper = new THREE.MeshStandardMaterial({
                    color: 0x2a2a2a, roughness: 0.6, metalness: 0.2
                });
                const matMirror = new THREE.MeshStandardMaterial({
                    color: 0x222222, roughness: 0.5
                });
                const matOrangeStripe = new THREE.MeshStandardMaterial({
                    color: 0xf5a623, roughness: 0.5
                });
                const matStep = new THREE.MeshStandardMaterial({
                    color: 0x444444, roughness: 0.7, metalness: 0.3
                });
                const matExhaust = new THREE.MeshStandardMaterial({
                    color: 0x555555, metalness: 0.5, roughness: 0.4
                });

                // =============================================
                // 1. CHASSIS FRAME (Rail dua batang)
                // =============================================
                const railGeo = new THREE.BoxGeometry(0.15, 0.2, 5.9);
                const railL = new THREE.Mesh(railGeo, matFrame.clone());
                railL.position.set(0.45, 0.55, 0.25);
                railL.castShadow = true;
                truckGroup.add(railL);
                const railR = new THREE.Mesh(railGeo, matFrame.clone());
                railR.position.set(-0.45, 0.55, 0.25);
                railR.castShadow = true;
                truckGroup.add(railR);

                // Cross members
                for (let i = -3; i <= 3; i++) {
                    const cross = new THREE.Mesh(
                        new THREE.BoxGeometry(0.75, 0.08, 0.12), matFrame
                    );
                    cross.position.set(0, 0.52, i * 0.8 + 0.25);
                    truckGroup.add(cross);
                }
                parts.chassis = railL;

                // =============================================
                // 2. KABIN ISUZU ELF (Cab Over Engine)
                // =============================================
                const cabinGroup = new THREE.Group();

                // --- Main cabin body ---
                const cabBody = new THREE.Mesh(
                    new THREE.BoxGeometry(2.05, 1.55, 1.7), matCabinWhite.clone()
                );
                cabBody.position.set(0, 1.50, 2.35);
                cabBody.castShadow = true;
                cabinGroup.add(cabBody);

                // --- Roof (slightly wider, rounded feel) ---
                const roof = new THREE.Mesh(
                    new THREE.BoxGeometry(2.1, 0.1, 1.75), matCabinWhite.clone()
                );
                roof.position.set(0, 2.33, 2.35);
                roof.castShadow = true;
                cabinGroup.add(roof);

                // Roof edge (subtle lip)
                const roofEdge = new THREE.Mesh(
                    new THREE.BoxGeometry(2.15, 0.04, 0.08), matBoxTrim
                );
                roofEdge.position.set(0, 2.36, 3.22);
                cabinGroup.add(roofEdge);

                // --- Windshield (large, slightly angled - ELF style) ---
                const wsShape = new THREE.Shape();
                wsShape.moveTo(-0.92, -0.55);
                wsShape.lineTo(0.92, -0.55);
                wsShape.lineTo(0.88, 0.55);
                wsShape.lineTo(-0.88, 0.55);
                wsShape.lineTo(-0.92, -0.55);

                const wsGeo = new THREE.ExtrudeGeometry(wsShape, {
                    depth: 0.04, bevelEnabled: true,
                    bevelThickness: 0.015, bevelSize: 0.015, bevelSegments: 2
                });
                const windshield = new THREE.Mesh(wsGeo, matGlass);
                windshield.position.set(0, 1.65, 3.22);
                windshield.rotation.x = -0.12;
                cabinGroup.add(windshield);

                // Windshield divider (center pillar)
                const wsDivider = new THREE.Mesh(
                    new THREE.BoxGeometry(0.03, 1.1, 0.06), matCabinWhite
                );
                wsDivider.position.set(0, 1.65, 3.24);
                cabinGroup.add(wsDivider);

                // --- Side Windows ---
                const sideWinGeo = new THREE.PlaneGeometry(1.2, 0.7);
                const sideWinL = new THREE.Mesh(sideWinGeo, matGlass.clone());
                sideWinL.position.set(1.03, 1.75, 2.35);
                sideWinL.rotation.y = Math.PI / 2;
                cabinGroup.add(sideWinL);
                const sideWinR = new THREE.Mesh(sideWinGeo, matGlass.clone());
                sideWinR.position.set(-1.03, 1.75, 2.35);
                sideWinR.rotation.y = -Math.PI / 2;
                cabinGroup.add(sideWinR);

                // Side window frames (A-pillar)
                const pillarGeo = new THREE.BoxGeometry(0.05, 1.1, 0.06);
                [1.04, -1.04].forEach(x => {
                    const pillarF = new THREE.Mesh(pillarGeo, matCabinWhite);
                    pillarF.position.set(x, 1.65, 2.95);
                    cabinGroup.add(pillarF);
                    const pillarR = new THREE.Mesh(pillarGeo, matCabinWhite);
                    pillarR.position.set(x, 1.65, 1.75);
                    cabinGroup.add(pillarR);
                });

                // --- Front Face (below windshield) ---
                const frontFace = new THREE.Mesh(
                    new THREE.BoxGeometry(2.05, 0.6, 0.08), matCabinWhite.clone()
                );
                frontFace.position.set(0, 0.95, 3.2);
                cabinGroup.add(frontFace);

                // --- Grille (horizontal slats - Isuzu ELF style) ---
                for (let i = 0; i < 4; i++) {
                    const slat = new THREE.Mesh(
                        new THREE.BoxGeometry(1.4, 0.04, 0.06), matGrille
                    );
                    slat.position.set(0, 0.78 + i * 0.08, 3.24);
                    cabinGroup.add(slat);
                }
                // Grille frame
                const grilleFrame = new THREE.Mesh(
                    new THREE.BoxGeometry(1.5, 0.38, 0.04), matGrille
                );
                grilleFrame.position.set(0, 0.92, 3.23);
                cabinGroup.add(grilleFrame);

                // --- Headlights (rectangular, Isuzu ELF style) ---
                const hlGeo = new THREE.BoxGeometry(0.28, 0.2, 0.1);
                const lightL = new THREE.Mesh(hlGeo, matHeadlight.clone());
                lightL.position.set(0.82, 0.95, 3.22);
                cabinGroup.add(lightL);
                const lightR = new THREE.Mesh(hlGeo, matHeadlight.clone());
                lightR.position.set(-0.82, 0.95, 3.22);
                cabinGroup.add(lightR);

                // Indicator lights (orange, below headlights)
                const indGeo = new THREE.BoxGeometry(0.25, 0.08, 0.08);
                const indL = new THREE.Mesh(indGeo, matIndicator.clone());
                indL.position.set(0.82, 0.8, 3.24);
                cabinGroup.add(indL);
                const indR = new THREE.Mesh(indGeo, matIndicator.clone());
                indR.position.set(-0.82, 0.8, 3.24);
                cabinGroup.add(indR);

                // --- Bumper (prominent, dark) ---
                const bumper = new THREE.Mesh(
                    new THREE.BoxGeometry(2.15, 0.25, 0.2), matBumper
                );
                bumper.position.set(0, 0.55, 3.28);
                bumper.castShadow = true;
                cabinGroup.add(bumper);

                // Bumper fog lights
                const fogGeo = new THREE.BoxGeometry(0.15, 0.1, 0.08);
                const fogL = new THREE.Mesh(fogGeo, matHeadlight.clone());
                fogL.position.set(0.7, 0.55, 3.38);
                cabinGroup.add(fogL);
                const fogR = new THREE.Mesh(fogGeo, matHeadlight.clone());
                fogR.position.set(-0.7, 0.55, 3.38);
                cabinGroup.add(fogR);

                // --- Side Mirrors (on stalks, ELF style) ---
                [1, -1].forEach(side => {
                    // Stalk
                    const stalk = new THREE.Mesh(
                        new THREE.BoxGeometry(0.35, 0.04, 0.04), matMirror
                    );
                    stalk.position.set(side * 1.2, 2.0, 2.9);
                    cabinGroup.add(stalk);
                    // Mirror housing
                    const mirrorHousing = new THREE.Mesh(
                        new THREE.BoxGeometry(0.06, 0.3, 0.2), matMirror
                    );
                    mirrorHousing.position.set(side * 1.38, 1.9, 2.9);
                    cabinGroup.add(mirrorHousing);
                    // Mirror glass
                    const mirrorGlass = new THREE.Mesh(
                        new THREE.PlaneGeometry(0.25, 0.15), matGlass
                    );
                    mirrorGlass.position.set(side * 1.38, 1.92, 2.91);
                    mirrorGlass.rotation.y = side > 0 ? Math.PI / 2 : -Math.PI / 2;
                    cabinGroup.add(mirrorGlass);
                });

                // --- Door handles ---
                const handleGeo = new THREE.BoxGeometry(0.12, 0.03, 0.03);
                const handleL = new THREE.Mesh(handleGeo, matMirror);
                handleL.position.set(1.04, 1.35, 2.2);
                cabinGroup.add(handleL);
                const handleR = new THREE.Mesh(handleGeo, matMirror);
                handleR.position.set(-1.04, 1.35, 2.2);
                cabinGroup.add(handleR);



                truckGroup.add(cabinGroup);
                parts.cabin = cabBody;
                parts.lampu = [lightL, lightR, indL, indR, fogL, fogR];

                // =============================================
                // 3. CARGO BOX (Bak tertutup - Isuzu ELF Box)
                // =============================================
                const boxGroup = new THREE.Group();

                // Main box body
                const cargoBox = new THREE.Mesh(
                    new THREE.BoxGeometry(2.15, 2.3, 4.2), matBox.clone()
                );
                cargoBox.position.set(0, 1.85, -0.6);
                cargoBox.castShadow = true;
                boxGroup.add(cargoBox);

                // Horizontal panel lines on sides (ELF box style)
                for (let i = 0; i < 6; i++) {
                    const lineGeo = new THREE.BoxGeometry(0.01, 0.015, 4.15);
                    [1.08, -1.08].forEach(x => {
                        const panelLine = new THREE.Mesh(lineGeo, matBoxTrim);
                        panelLine.position.set(x, 1.05 + i * 0.38, -0.6);
                        boxGroup.add(panelLine);
                    });
                }

                // Horizontal panel lines on rear
                for (let i = 0; i < 6; i++) {
                    const rearLine = new THREE.Mesh(
                        new THREE.BoxGeometry(2.1, 0.015, 0.01), matBoxTrim
                    );
                    rearLine.position.set(0, 1.05 + i * 0.38, -2.7);
                    boxGroup.add(rearLine);
                }

                // Box bottom rail / frame
                const boxRail = new THREE.Mesh(
                    new THREE.BoxGeometry(2.2, 0.08, 4.25), matFrame
                );
                boxRail.position.set(0, 0.68, -0.6);
                boxGroup.add(boxRail);

                // Orange reflector stripe (ELF signature)
                const stripeL = new THREE.Mesh(
                    new THREE.BoxGeometry(0.015, 0.08, 1.2), matOrangeStripe
                );
                stripeL.position.set(1.085, 0.9, -1.8);
                boxGroup.add(stripeL);
                const stripeR = new THREE.Mesh(
                    new THREE.BoxGeometry(0.015, 0.08, 1.2), matOrangeStripe
                );
                stripeR.position.set(-1.085, 0.9, -1.8);
                boxGroup.add(stripeR);

                // Rear orange reflector
                const stripeRear = new THREE.Mesh(
                    new THREE.BoxGeometry(1.0, 0.08, 0.015), matOrangeStripe
                );
                stripeRear.position.set(0, 0.9, -2.71);
                boxGroup.add(stripeRear);

                // Rear tail lights
                const tailGeo = new THREE.BoxGeometry(0.2, 0.3, 0.06);
                const tailL = new THREE.Mesh(tailGeo, matTaillight.clone());
                tailL.position.set(0.9, 1.1, -2.72);
                boxGroup.add(tailL);
                const tailR = new THREE.Mesh(tailGeo, matTaillight.clone());
                tailR.position.set(-0.9, 1.1, -2.72);
                boxGroup.add(tailR);

                // Rear indicators
                const rIndGeo = new THREE.BoxGeometry(0.2, 0.1, 0.06);
                const rIndL = new THREE.Mesh(rIndGeo, matIndicator);
                rIndL.position.set(0.9, 1.35, -2.72);
                boxGroup.add(rIndL);
                const rIndR = new THREE.Mesh(rIndGeo, matIndicator);
                rIndR.position.set(-0.9, 1.35, -2.72);
                boxGroup.add(rIndR);

                // Rear door handle
                const rDoorHandle = new THREE.Mesh(
                    new THREE.BoxGeometry(0.3, 0.04, 0.04), matMirror
                );
                rDoorHandle.position.set(0, 1.5, -2.73);
                boxGroup.add(rDoorHandle);

                // Vertical reinforcement bars on sides
                for (let i = 0; i < 4; i++) {
                    [1.085, -1.085].forEach(x => {
                        const vBar = new THREE.Mesh(
                            new THREE.BoxGeometry(0.015, 1.8, 0.04), matBoxTrim
                        );
                        vBar.position.set(x, 1.6, 0.9 - i * 1.1);
                        boxGroup.add(vBar);
                    });
                }

                truckGroup.add(boxGroup);
                parts.box = cargoBox;


                // =============================================
                // 5. WHEELS (Front single, Rear dual - ELF style)
                // =============================================
                const wheelGeo = new THREE.CylinderGeometry(0.42, 0.42, 0.22, 28);
                wheelGeo.rotateZ(Math.PI / 2);
                const rimGeo = new THREE.CylinderGeometry(0.2, 0.2, 0.23, 16);
                rimGeo.rotateZ(Math.PI / 2);
                const hubGeo = new THREE.CylinderGeometry(0.08, 0.08, 0.24, 12);
                hubGeo.rotateZ(Math.PI / 2);

                parts.wheels = [];

                // Front wheels (single)
                [{x: 0.95, z: 2.35}, {x: -0.95, z: 2.35}].forEach(pos => {
                    const wheel = new THREE.Mesh(wheelGeo, matTire.clone());
                    wheel.position.set(pos.x, 0.42, pos.z);
                    wheel.castShadow = true;
                    truckGroup.add(wheel);

                    const rim = new THREE.Mesh(rimGeo, matRim);
                    rim.position.copy(wheel.position);
                    truckGroup.add(rim);

                    const hub = new THREE.Mesh(hubGeo, matRim);
                    hub.position.copy(wheel.position);
                    truckGroup.add(hub);

                    parts.wheels.push(wheel);
                });

                // Rear wheels (dual - inner + outer per side)
                [{x: 0.82, z: -1.6}, {x: -0.82, z: -1.6}].forEach(pos => {
                    // Inner wheel
                    const wInner = new THREE.Mesh(wheelGeo, matTire.clone());
                    wInner.position.set(pos.x, 0.42, pos.z);
                    wInner.castShadow = true;
                    truckGroup.add(wInner);
                    const rimInner = new THREE.Mesh(rimGeo, matRim);
                    rimInner.position.copy(wInner.position);
                    truckGroup.add(rimInner);

                    // Outer wheel
                    const outerX = pos.x > 0 ? pos.x + 0.24 : pos.x - 0.24;
                    const wOuter = new THREE.Mesh(wheelGeo, matTire.clone());
                    wOuter.position.set(outerX, 0.42, pos.z);
                    wOuter.castShadow = true;
                    truckGroup.add(wOuter);
                    const rimOuter = new THREE.Mesh(rimGeo, matRim);
                    rimOuter.position.copy(wOuter.position);
                    truckGroup.add(rimOuter);

                    parts.wheels.push(wInner);
                    parts.wheels.push(wOuter);
                });

                scene.add(truckGroup);
            }

            // --- ANIMATION LOGIC ---
            function applyDamageEffects() {
                if (vehicleStatus.ban === 'danger') {
                    parts.wheels.forEach(w => damagedParts.push(w));
                }
                if (vehicleStatus.rem === 'danger') {
                    damagedParts.push(parts.chassis);
                }
                if (vehicleStatus.mesin === 'danger') {
                    damagedParts.push(parts.cabin);
                }
                if (vehicleStatus.lampu === 'danger') {
                    parts.lampu.forEach(l => damagedParts.push(l));
                }
            }

            function animatePulse() {
                if (damagedParts.length > 0) {
                    pulseTime += 0.08;
                    // Sine wave 0.0 -> 1.0 -> 0.0
                    const intensity = (Math.sin(pulseTime) + 1) * 0.5;

                    damagedParts.forEach(part => {
                        if (part.material) {
                            part.material.emissive.setHex(0xff0000); // Red
                            part.material.emissiveIntensity = intensity;
                        }
                    });
                }
            }

            // --- MOUSE HOVER LOGIC (Optional Override) ---
            function highlightPart(partName) {
                // Temporary highlight logic for mouseover
                let targetList = [];
                if (partName === 'wheels') targetList = parts.wheels;
                else if (partName === 'rem') targetList = [parts.chassis];
                else if (partName === 'lampu') targetList = parts.lampu;
                else if (partName === 'cabin') targetList = [parts.cabin];

                targetList.forEach(obj => {
                    if (obj && obj.material) {
                        obj.material.emissive.setHex(0xffaa00); // Orange on hover
                        obj.material.emissiveIntensity = 0.5;
                    }
                });
            }

            function resetHighlight() {
                // Reset everything to black (off)
                truckGroup.traverse(child => {
                    if (child.isMesh && child.material) {
                        child.material.emissive.setHex(0x000000);
                        child.material.emissiveIntensity = 0;
                    }
                });
                // Re-apply damage pulse immediately so it doesn't look broken
                // The animate loop picks it up, but we ensure color is red again
            }

            function animate() {
                requestAnimationFrame(animate);

                if (isAutoRotate && truckGroup) truckGroup.rotation.y += 0.005;

                controls.update();
                animatePulse(); // Continuous pulsing
                renderer.render(scene, camera);
            }

            function onWindowResize() {
                const container = document.getElementById('vehicle-3d');
                if (!container) return;
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            }

            // --- BUTTON HANDLERS ---
            window.resetCamera = function () {
                controls.reset();
                isAutoRotate = false;
            };
            window.toggleAutoRotate = function () {
                isAutoRotate = !isAutoRotate;
            };
            window.toggleWireframe = function () {
                if (truckGroup) {
                    truckGroup.traverse(c => {
                        if (c.isMesh) c.material.wireframe = !c.material.wireframe;
                    });
                }
            };
            window.highlightPart = highlightPart;
            window.resetHighlight = resetHighlight;

            // Start — FIX: Bungkus dalam DOMContentLoaded agar layout ter-compute sebelum init
            // dan tambahkan try-catch agar error tampil, bukan loading spinner stuck selamanya
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    init();
                } catch (err) {
                    console.error('[3D Visual] Gagal memuat model:', err);
                    const loader = document.getElementById('loader');
                    if (loader) {
                        loader.innerHTML = `
                                    <div class="text-center text-white p-4">
                                        <i class="bi bi-exclamation-triangle-fill text-warning fs-1 d-block mb-3"></i>
                                        <strong>Gagal Memuat Model 3D</strong>
                                        <p class="small mt-2 text-white-50">
                                            ${err.message || 'Periksa konsol browser untuk detail error.'}
                                        </p>
                                    </div>`;
                    }
                }
            });
        </script>
    @endpush
@endsection