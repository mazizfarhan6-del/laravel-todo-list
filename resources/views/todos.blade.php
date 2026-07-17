<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium To-Do List Plus</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        .gradient-text {
            background: linear-gradient(45deg, #0d6efd, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card {
            border-radius: 16px !important;
            transition: all 0.3s ease;
        }
        /* PERBAIKAN: Memaksa scrollbar horizontal aktif & mulus di iOS */
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch; /* Membuat swipe di iPhone terasa ringan/smooth */
            width: 100%;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        .progress {
            border-radius: 20px;
            background-color: var(--bs-secondary-bg);
        }
    </style>

    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    </script>
</head>
<body class="bg-body-tertiary text-body">
    @php
        $today = date('Y-m-d');
        
        $urgentCount = $todos->filter(function($todo) use ($today) {
            return $todo->due_date && $todo->due_date <= $today && $todo->status != 'completed';
        })->count();

        $totalTodos = $todos->count();
        $completedTodos = $todos->where('status', 'completed')->count();
        $progressPercentage = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100) : 0;
    @endphp

    <div class="container py-5" style="max-width: 1140px;">
        @if($urgentCount > 0)
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 p-3 rounded-4" role="alert">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <strong>⚠️ PENGINGAT:</strong> Kamu punya <strong>{{ $urgentCount }} tugas</strong> yang belum selesai dan sudah mendekati/melewati batas waktu!
                        </div>
                    </div>
                    <button class="btn btn-sm btn-danger px-3 rounded-3" onclick="playNotificationSound()">
                        <i class="bi bi-volume-up-fill me-1"></i> Tes Suara
                    </button>
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check2-circle text-primary fs-2"></i>
                <h2 class="fw-bold m-0 gradient-text">MAZIZ To-Do List</h2>
            </div>
            <button id="themeToggle" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
                <i id="themeIcon" class="bi bi-moon-stars-fill"></i> <span id="themeText">Dark Mode</span>
            </button>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h6 class="fw-bold text-secondary mb-1"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Progress Penyelesaian</h6>
                        <span class="text-muted small">Terlesaikan <strong>{{ $completedTodos }}</strong> dari <strong>{{ $totalTodos }}</strong> total tugas aktif.</span>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small">{{ $progressPercentage }}% Selesai</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 role="progressbar" 
                                 style="width: {{ $progressPercentage }}%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4 mb-5">
            <!-- FORM TAMBAH TUGAS -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-3 text-body-emphasis"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Tugas Baru</h5>
                        <form action="{{ route('todos.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Judul Tugas</label>
                                <input type="text" name="title" class="form-control" placeholder="Nama tugas kamu..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Kategori</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Prioritas</label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">🟢 Low (Rendah)</option>
                                    <option value="medium" selected>🟡 Medium (Sedang)</option>
                                    <option value="high">🔴 High (Penting)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Batas Waktu (Deadline)</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Deskripsi <span class="text-muted fw-normal">(Opsional)</span></label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Detail catatan tugas..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mt-2 shadow-sm">
                                <i class="bi bi-file-earmark-plus-fill me-1"></i> Simpan Tugas
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DAFTAR TUGAS AKTIF -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <h5 class="card-title fw-bold m-0 text-body-emphasis"><i class="bi bi-list-task me-2 text-primary"></i>Daftar Tugas Aktif</h5>
                            
                            <form action="{{ route('todos.index') }}" method="GET" class="d-flex gap-2 align-items-center flex-wrap flex-md-nowrap">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari..." value="{{ request('search') }}">
                                </div>
                                <select name="category_id" class="form-select form-select-sm" style="min-width: 130px;">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">Cari</button>
                                @if(request('search') || request('category_id'))
                                    <a href="{{ route('todos.index') }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></a>
                                @endif
                            </form>
                        </div>
                        
                        @if($todos->isEmpty())
                            <div class="text-center py-5">
                                <i class="bi bi-clipboard2-check text-muted mb-2" style="font-size: 3rem;"></i>
                                <p class="text-muted m-0">Tugas tidak ditemukan atau belum ada daftar tugas aktif.</p>
                            </div>
                        @else
                            <div class="table-responsive border">
                                <!-- PERBAIKAN: Menambahkan min-width agar tabel memiliki ruang horizontal dan memicu efek swipe di screen kecil -->
                                <table class="table table-hover align-middle mb-0" style="min-width: 750px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="ps-3 py-3">Tugas</th>
                                            <th class="py-3">Kategori</th>
                                            <th class="py-3">Prioritas</th>
                                            <th class="py-3">Deadline</th>
                                            <th class="py-3" style="width: 140px;">Status</th>
                                            <th class="text-center py-3" style="width: 80px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($todos as $todo)
                                            @php
                                                $isUrgent = $todo->due_date && $todo->due_date <= $today && $todo->status != 'completed';
                                            @endphp
                                            <tr class="{{ $isUrgent ? 'table-danger' : '' }}">
                                                <td class="ps-3 py-3 {{ $todo->status == 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                                    <div class="fw-bold">{{ $todo->title }}</div>
                                                    <small class="text-muted d-block">{{ $todo->description ?? '-' }}</small>
                                                </td>
                                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis border px-2 py-1.5">{{ $todo->category->name }}</span></td>
                                                <td>
                                                    @if($todo->priority == 'high') <span class="badge bg-danger-subtle text-danger border px-2 py-1.5"><i class="bi bi-fire me-1"></i>High</span>
                                                    @elseif($todo->priority == 'medium') <span class="badge bg-warning-subtle text-warning-emphasis border px-2 py-1.5"><i class="bi bi-exclamation-circle me-1"></i>Medium</span>
                                                    @else <span class="badge bg-success-subtle text-success border px-2 py-1.5"><i class="bi bi-arrow-down-short me-1"></i>Low</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="{{ $isUrgent ? 'text-danger fw-bold' : '' }} small">
                                                        <i class="bi bi-calendar3 me-1"></i>{{ $todo->due_date ? date('d M Y', strtotime($todo->due_date)) : '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <form action="{{ route('todos.updateStatus', $todo->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                                                            <option value="pending" {{ $todo->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                            <option value="in_progress" {{ $todo->status == 'in_progress' ? 'selected' : '' }}>🚀 Progress</option>
                                                            <option value="completed" {{ $todo->status == 'completed' ? 'selected' : '' }}>✅ Selesai</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td class="text-center pe-3">
                                                    <form action="{{ route('todos.destroy', $todo->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" title="Pindahkan ke sampah">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- TAMPILAN KOTAK SAMPAH (TRASH) -->
        <div class="card border-0 shadow-sm bg-body-secondary">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold text-muted mb-3"><i class="bi bi-trash3 me-2"></i>Kotak Sampah (Soft Deleted)</h6>
                @if($trashedTodos->isEmpty())
                    <p class="text-muted small m-0"><i class="bi bi-info-circle me-1"></i>Kotak sampah kosong.</p>
                @else
                    <div class="table-responsive border bg-body">
                        <!-- PERBAIKAN: Menambahkan min-width juga pada tabel sampah -->
                        <table class="table table-sm align-middle mb-0" style="min-width: 600px;">
                            <thead>
                                <tr class="table-secondary">
                                    <th class="ps-3 py-2">Nama Tugas</th>
                                    <th class="py-2">Kategori</th>
                                    <th class="text-center py-2" style="width: 220px;">Aksi Pemulihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashedTodos as $trashed)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <div class="text-decoration-line-through text-muted small fw-semibold">{{ $trashed->title }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border px-2 py-1">{{ $trashed->category->name }}</span></td>
                                        <td class="text-center pe-3 py-2">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('todos.restore', $trashed->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm py-1 px-3 rounded-3"><i class="bi bi-arrow-counterclockwise me-1"></i>Pulihkan</button>
                                                </form>
                                                <form action="{{ route('todos.forceDelete', $trashed->id) }}" method="POST" onsubmit="return confirm('Hapus permanen? Data tidak bisa dikembalikan!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm py-1 rounded-3"><i class="bi bi-x-octagon-fill me-1"></i>Hapus Selamanya</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btn = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');

        function updateButtonUI(theme) {
            if (theme === 'dark') {
                icon.className = 'bi bi-sun-fill text-warning'; text.textContent = 'Light Mode';
                btn.classList.replace('btn-outline-primary', 'btn-outline-warning');
            } else {
                icon.className = 'bi bi-moon-stars-fill'; text.textContent = 'Dark Mode';
                btn.classList.replace('btn-outline-warning', 'btn-outline-primary');
            }
        }

        updateButtonUI(document.documentElement.getAttribute('data-bs-theme'));
        btn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateButtonUI(newTheme);
        });

        const urgentCount = {{ $urgentCount }};
        function playNotificationSound() {
            try {
                const context = new (window.AudioContext || window.webkitAudioContext)();
                const osc1 = context.createOscillator();
                const gain1 = context.createGain();
                osc1.type = 'sine'; osc1.frequency.setValueAtTime(523.25, context.currentTime);
                gain1.gain.setValueAtTime(0.3, context.currentTime); gain1.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.3);
                osc1.connect(gain1); gain1.connect(context.destination);
                osc1.start(); osc1.stop(context.currentTime + 0.3);
                setTimeout(() => {
                    const osc2 = context.createOscillator();
                    const gain2 = context.createGain();
                    osc2.type = 'sine'; osc2.frequency.setValueAtTime(783.99, context.currentTime);
                    gain2.gain.setValueAtTime(0.3, context.currentTime); gain2.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.4);
                    osc2.connect(gain2); gain2.connect(context.destination);
                    osc2.start(); osc2.stop(context.currentTime + 0.4);
                }, 120);
            } catch (e) {}
        }

        function triggerBrowserNotification(count) {
            if (Notification.permission === "granted") {
                new Notification("⚠️ Pengingat Tugas Lapangan!", {
                    body: `Kamu memiliki ${count} tugas belum selesai yang sudah jatuh tempo!`,
                });
            }
        }

        if (urgentCount > 0) {
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") triggerBrowserNotification(urgentCount);
                });
            } else {
                triggerBrowserNotification(urgentCount);
            }
            document.addEventListener('click', () => { playNotificationSound(); }, { once: true });
        }
    </script>
</body>
</html>