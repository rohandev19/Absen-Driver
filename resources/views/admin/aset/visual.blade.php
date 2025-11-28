@extends('admin.layouts.app')

@section('title', 'Visual Check - ' . $vehicle->plate_number)

@section('content')
    <div class="container-fluid p-0">

        {{-- HEADER HALAMAN --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-0">Kondisi Kendaraan</h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-primary fs-6">{{ $vehicle->plate_number }}</span>
                    <span class="text-muted small border-start ps-2 ms-1">
                        {{ $vehicle->type }} | Update:
                        {{ $lastLog ? \Carbon\Carbon::parse($lastLog->time_out)->format('d/m/Y H:i') : '-' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('admin.maintenance.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
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
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cpu text-primary me-2"></i>System Diagnostics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="vstack gap-3">

                            {{-- 1. KONDISI BAN --}}
                            <div class="diagnostic-item p-3 rounded border {{ $status['ban'] == 'danger' ? 'border-danger bg-danger-subtle' : '' }}"
                                onmouseover="highlightPart('wheels')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <i
                                            class="bi bi-vinyl fs-4 {{ $status['ban'] == 'danger' ? 'text-danger' : 'text-success' }}"></i>
                                        <div>
                                            <div class="fw-bold">Kondisi Ban</div>
                                            <div class="small text-muted">Tekanan & Fisik</div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill {{ $status['ban'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                        {{ $status['ban'] == 'danger' ? 'PERIKSA' : 'OK' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 2. SISTEM REM --}}
                            <div class="diagnostic-item p-3 rounded border {{ $status['rem'] == 'danger' ? 'border-danger bg-danger-subtle' : '' }}"
                                onmouseover="highlightPart('rem')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <i
                                            class="bi bi-sign-stop-fill fs-4 {{ $status['rem'] == 'danger' ? 'text-danger' : 'text-success' }}"></i>
                                        <div>
                                            <div class="fw-bold">Sistem Rem</div>
                                            <div class="small text-muted">Kampas & Minyak</div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill {{ $status['rem'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                        {{ $status['rem'] == 'danger' ? 'BAHAYA' : 'OK' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 3. KELISTRIKAN --}}
                            <div class="diagnostic-item p-3 rounded border {{ $status['lampu'] == 'danger' ? 'border-danger bg-danger-subtle' : '' }}"
                                onmouseover="highlightPart('lampu')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <i
                                            class="bi bi-lightbulb-fill fs-4 {{ $status['lampu'] == 'danger' ? 'text-danger' : 'text-success' }}"></i>
                                        <div>
                                            <div class="fw-bold">Kelistrikan</div>
                                            <div class="small text-muted">Lampu & Sein</div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge rounded-pill {{ $status['lampu'] == 'danger' ? 'bg-danger' : 'bg-success' }}">
                                        {{ $status['lampu'] == 'danger' ? 'RUSAK' : 'OK' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 4. MESIN --}}
                            <div class="diagnostic-item p-3 rounded border {{ $status['mesin'] == 'danger' ? 'border-danger bg-danger-subtle' : '' }}"
                                onmouseover="highlightPart('cabin')" onmouseout="resetHighlight()">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <i
                                            class="bi bi-gear-wide-connected fs-4 {{ $status['mesin'] == 'danger' ? 'text-danger' : 'text-success' }}"></i>
                                        <div>
                                            <div class="fw-bold">Mesin & Oli</div>
                                            <div class="small text-muted">Interval Servis</div>
                                        </div>
                                    </div>
                                    @if ($status['mesin'] == 'danger')
                                        <span class="badge bg-danger rounded-pill">DUE</span>
                                    @else
                                        <span class="badge bg-success rounded-pill">PRIMA</span>
                                    @endif
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
                            <div class="text-end mt-2 small text-muted">
                                Driver: <strong>{{ $lastLog->driver->full_name ?? '-' }}</strong>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

        <script>
            // --- VARIABLES ---
            let scene, camera, renderer, controls;
            let truckGroup;
            let parts = {};
            let isAutoRotate = false;
            let damagedParts = [];
            let pulseTime = 0;

            // Data Status dari Controller
            const vehicleStatus = {
                ban: "{{ $status['ban'] }}",
                rem: "{{ $status['rem'] }}",
                lampu: "{{ $status['lampu'] }}",
                mesin: "{{ $status['mesin'] }}"
            };

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

                // --- Materials ---
                const matCabin = new THREE.MeshStandardMaterial({
                    color: 0xf1c40f,
                    roughness: 0.3,
                    metalness: 0.1
                }); // Yellow
                const matChassis = new THREE.MeshStandardMaterial({
                    color: 0x111111,
                    roughness: 0.9
                }); // Dark Grey
                const matBox = new THREE.MeshStandardMaterial({
                    color: 0xecf0f1,
                    roughness: 0.5
                }); // White
                const matGlass = new THREE.MeshStandardMaterial({
                    color: 0x3498db,
                    transparent: true,
                    opacity: 0.6,
                    roughness: 0.1
                });
                const matTire = new THREE.MeshStandardMaterial({
                    color: 0x222222
                });
                const matRim = new THREE.MeshStandardMaterial({
                    color: 0xbdc3c7,
                    metalness: 0.5
                });
                const matLight = new THREE.MeshStandardMaterial({
                    color: 0xffffff,
                    emissive: 0xffffcc,
                    emissiveIntensity: 0.5
                });
                const matDetail = new THREE.MeshStandardMaterial({
                    color: 0x333333
                }); // For mirrors/handles

                // 1. CHASSIS
                const chassis = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.2, 5.5), matChassis.clone());
                chassis.position.y = 0.6;
                chassis.castShadow = true;
                truckGroup.add(chassis);
                parts.chassis = chassis;

                // 2. KABIN DEPAN (Group)
                const cabinGroup = new THREE.Group();

                // Base Cabin Block
                const cabBase = new THREE.Mesh(new THREE.BoxGeometry(2, 1.4, 1.6), matCabin.clone());
                cabBase.position.set(0, 1.4, 1.8);
                cabBase.castShadow = true;
                cabinGroup.add(cabBase);

                // --- KACA DEPAN ---
                const windshieldShape = new THREE.Shape();
                // Koordinat Shape (X, Y) dalam 2D sebelum di-extrude
                // Disesuaikan agar lebih presisi
                windshieldShape.moveTo(-0.9, -0.35); // Kiri Bawah
                windshieldShape.lineTo(0.9, -0.35);  // Kanan Bawah
                windshieldShape.lineTo(0.85, 0.35);  // Kanan Atas (Trapesium halus)
                windshieldShape.lineTo(-0.85, 0.35); // Kiri Atas
                windshieldShape.lineTo(-0.9, -0.35); // Tutup Jalur

                const windshieldGeo = new THREE.ExtrudeGeometry(windshieldShape, {
                    depth: 0.05,        // Tipis saja
                    bevelEnabled: true, // Bevel agar pinggiran halus
                    bevelThickness: 0.02,
                    bevelSize: 0.02,
                    bevelSegments: 2
                });
                const windshield = new THREE.Mesh(windshieldGeo, matGlass);

                // KOORDINAT BARU YANG LEBIH PAS:
                // Y: 1.8 (Posisi vertikal di tengah atas kabin)
                // Z: 2.62 (Maju sedikit dari kabin, menempel di depan)
                // Rotasi X: -0.1 (Miring sedikit ke belakang agar aerodinamis tapi tidak melayang)
                windshield.position.set(0, 1.8, 2.62);
                windshield.rotation.x = -0.1;

                cabinGroup.add(windshield);
                // ----------------------------

                // Front Lights (Lampu)
                const lightL = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.2, 0.1), matLight.clone());
                lightL.position.set(0.6, 0.9, 2.65);
                cabinGroup.add(lightL);
                const lightR = new THREE.Mesh(new THREE.BoxGeometry(0.4, 0.2, 0.1), matLight.clone());
                lightR.position.set(-0.6, 0.9, 2.65);
                cabinGroup.add(lightR);

                // Grille / Bumper Detail
                const bumper = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.3, 0.15), matDetail);
                bumper.position.set(0, 0.6, 2.65);
                cabinGroup.add(bumper);

                // Side Mirrors (Spion)
                const mirrorGeo = new THREE.BoxGeometry(0.1, 0.4, 0.2);
                const mirrorL = new THREE.Mesh(mirrorGeo, matDetail);
                mirrorL.position.set(1.1, 1.8, 2.0);
                mirrorL.rotation.y = 0.2;
                cabinGroup.add(mirrorL);
                const mirrorR = new THREE.Mesh(mirrorGeo, matDetail);
                mirrorR.position.set(-1.1, 1.8, 2.0);
                mirrorR.rotation.y = -0.2;
                cabinGroup.add(mirrorR);

                truckGroup.add(cabinGroup);
                parts.cabin = cabBase; // Logic highlights the main block
                parts.lampu = [lightL, lightR];

                // 3. BOX BELAKANG
                const box = new THREE.Mesh(new THREE.BoxGeometry(2.1, 2.2, 3.8), matBox.clone());
                box.position.set(0, 1.8, -0.8);
                box.castShadow = true;
                truckGroup.add(box);
                parts.box = box;

                // 4. WHEELS
                const wheelGeo = new THREE.CylinderGeometry(0.4, 0.4, 0.25, 24);
                wheelGeo.rotateZ(Math.PI / 2);

                const wheelPositions = [{
                    x: 0.9,
                    z: 1.8
                }, {
                    x: -0.9,
                    z: 1.8
                }, {
                    x: 0.9,
                    z: -1.5
                }, {
                    x: -0.9,
                    z: -1.5
                }];

                parts.wheels = [];
                wheelPositions.forEach(pos => {
                    const wheel = new THREE.Mesh(wheelGeo, matTire.clone());
                    wheel.position.set(pos.x, 0.4, pos.z);
                    wheel.castShadow = true;
                    truckGroup.add(wheel);

                    const rim = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.2, 0.26, 16).rotateZ(Math.PI / 2),
                        matRim);
                    rim.position.copy(wheel.position);
                    truckGroup.add(rim);

                    parts.wheels.push(wheel);
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

            // Start
            init();
        </script>
    @endpush
@endsection