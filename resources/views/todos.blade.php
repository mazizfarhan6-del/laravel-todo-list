@extends('layouts.app')

@section('title', 'MAZIZ To-Do List - Dashboard Tugas')
@section('page_title', 'Dashboard Tugas')

@section('content')
@php
    $today = date('Y-m-d');
    
    $urgentCount = $todos->filter(function($todo) use ($today) {
        return $todo->due_date && $todo->due_date <= $today && $todo->status != 'completed';
    })->count();

    $totalTodos = $todos->count();
    $completedTodos = $todos->where('status', 'completed')->count();
    $inProgressTodos = $todos->where('status', 'in_progress')->count();
    $pendingTodos = $todos->where('status', 'pending')->count();
    $progressPercentage = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100) : 0;
@endphp

<!-- ============================================================== -->
<!-- 1. ALERT JIKA ADA TUGAS MENDESAK / OVERDUE                      -->
<!-- ============================================================== -->
@if($urgentCount > 0)
    <div id="urgentSection" class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-danger text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-danger">Perhatian: Tugas Mendesak</h6>
                <small class="text-danger-emphasis">Terdapat <strong>{{ $urgentCount }} tugas</strong> yang mendekati atau telah melewati batas waktu pengerjaan.</small>
            </div>
        </div>
        <button class="btn btn-sm btn-danger px-3 rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="playNotificationSound()">
            <i class="bi bi-volume-up-fill"></i>
            <span>Tes Audio Pengingat</span>
        </button>
    </div>
@endif

<!-- ============================================================== -->
<!-- 2. SUMMARY & PROGRESS STATS CARDS                              -->
<!-- ============================================================== -->
<div class="row g-3 mb-4">
    <!-- Progress Bar Card -->
    <div class="col-xl-4 col-md-6">
        <div class="modern-card p-3 h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="stat-label">Tingkat Penyelesaian</span>
                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1">{{ $progressPercentage }}%</span>
            </div>
            <div>
                <div class="progress mb-2" style="height: 10px; border-radius: 999px; background-color: #E2E8F0;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                         role="progressbar" 
                         style="width: {{ $progressPercentage }}%;" 
                         aria-valuenow="{{ $progressPercentage }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between text-muted small">
                    <span><strong>{{ $completedTodos }}</strong> selesai</span>
                    <span><strong>{{ $totalTodos }}</strong> total tugas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Tasks Count -->
    <div class="col-xl-4 col-md-6">
        <div class="modern-card stat-card h-100">
            <div class="stat-icon primary">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value">{{ $totalTodos - $completedTodos }}</div>
                <div class="stat-label">Tugas Aktif Berjalan</div>
            </div>
        </div>
    </div>

    <!-- Completed Tasks Count -->
    <div class="col-xl-4 col-md-12">
        <div class="modern-card stat-card h-100">
            <div class="stat-icon success">
                <i class="bi bi-check2-all"></i>
            </div>
            <div>
                <div class="stat-value">{{ $completedTodos }}</div>
                <div class="stat-label">Tugas Selesai Tuntas</div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- 3. MAIN SECTION: DAFTAR TUGAS UTAMA (FULL WIDTH & SORTABLE)     -->
<!-- ============================================================== -->
<div class="modern-card mb-4">
    <!-- Toolbar: Search & Filter Kategori -->
    <div class="modern-card-header flex-column flex-md-row gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-check text-primary fs-5"></i>
            <h5 class="modern-card-title">Daftar Tugas</h5>
        </div>

        <form action="{{ route('todos.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-md-nowrap w-100 w-md-auto justify-content-end">
            <div class="input-group input-group-sm" style="max-width: 220px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari judul tugas..." value="{{ request('search') }}">
            </div>

            <select name="category_id" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm rounded-3">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            
            @if(request('search') || request('category_id'))
                <a href="{{ route('todos.index') }}" class="btn btn-sm btn-outline-secondary rounded-3" title="Reset Filter">
                    <i class="bi bi-x-circle"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Task List Body -->
    <div class="modern-card-body">
        @if($todos->isEmpty())
            <div class="text-center py-5">
                <div class="stat-icon bg-light text-muted mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                    <i class="bi bi-clipboard2-check"></i>
                </div>
                <h6 class="fw-bold text-dark">Tidak Ada Tugas</h6>
                <p class="text-muted small mb-0">Belum ada tugas yang tersedia. Gunakan tombol <strong>+ Tambah Tugas</strong> di sudut kanan atas untuk membuat tugas baru.</p>
            </div>
        @else
            <!-- Helper Reorder Notice -->
            <div class="d-flex align-items-center justify-content-between mb-3 text-muted small">
                <span><i class="bi bi-grip-vertical text-primary me-1"></i> Tarik penunjuk urutan untuk mengatur prioritas daftar tugas.</span>
                <span class="badge bg-light text-secondary border">{{ $todos->count() }} Tugas Terdaftar</span>
            </div>

            <!-- Sortable List Items -->
            <div id="sortableTodoList" class="todo-items-container">
                @foreach($todos as $todo)
                    @php
                        $isUrgent = $todo->due_date && $todo->due_date <= $today && $todo->status != 'completed';
                        $isCompleted = $todo->status == 'completed';
                    @endphp

                    <div class="task-item-card {{ $isUrgent ? 'task-urgent-indicator' : '' }} {{ $isCompleted ? 'task-item-completed' : '' }}" data-id="{{ $todo->id }}">
                        <!-- Drag Handle & Checkbox -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="drag-handle" title="Tahan dan geser untuk memindahkan">
                                <i class="bi bi-grip-vertical fs-5"></i>
                            </span>

                            <!-- Micro-animation Checkbox with Bounce -->
                            <form action="{{ route('todos.updateStatus', $todo->id) }}" method="POST" id="statusForm-{{ $todo->id }}" class="m-0">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="{{ $isCompleted ? 'pending' : 'completed' }}">
                                <label class="task-checkbox-wrapper" title="{{ $isCompleted ? 'Tandai belum selesai' : 'Tandai selesai' }}">
                                    <input type="checkbox" class="task-checkbox-input" {{ $isCompleted ? 'checked' : '' }} onchange="document.getElementById('statusForm-{{ $todo->id }}').submit()">
                                </label>
                            </form>
                        </div>

                        <!-- Task Details (Title, Category, Priority, Description) -->
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                <span class="fw-bold task-title-text {{ $isCompleted ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                    {{ $todo->title }}
                                </span>

                                <!-- Priority Badges (Clean Bootstrap Icons) -->
                                @if($todo->priority == 'high')
                                    <span class="badge-clean badge-priority-high"><i class="bi bi-exclamation-octagon-fill"></i> Tinggi</span>
                                @elseif($todo->priority == 'medium')
                                    <span class="badge-clean badge-priority-medium"><i class="bi bi-dash-circle-fill"></i> Sedang</span>
                                @else
                                    <span class="badge-clean badge-priority-low"><i class="bi bi-check-circle-fill"></i> Rendah</span>
                                @endif

                                <!-- Category Badge -->
                                <span class="badge-clean badge-category">
                                    <i class="bi bi-folder2 text-primary"></i>
                                    {{ $todo->category->name }}
                                </span>
                            </div>

                            @if($todo->description)
                                <div class="text-muted small text-truncate" style="max-width: 600px;">
                                    {{ $todo->description }}
                                </div>
                            @endif
                        </div>

                        <!-- Deadline & Status Selector & Actions -->
                        <div class="d-flex align-items-center gap-3">
                            <!-- Deadline Info -->
                            <div class="text-end d-none d-md-block">
                                <span class="small d-block {{ $isUrgent ? 'text-danger fw-bold' : 'text-muted' }}">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $todo->due_date ? date('d M Y', strtotime($todo->due_date)) : '-' }}
                                </span>
                            </div>

                            <!-- Status Dropdown (Clean Text, No Emojis) -->
                            <form action="{{ route('todos.updateStatus', $todo->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm fw-semibold" style="min-width: 130px;" onchange="this.form.submit()">
                                    <option value="pending" {{ $todo->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $todo->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $todo->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </form>

                            <!-- Delete Button -->
                            <form action="{{ route('todos.destroy', $todo->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Pindahkan ke Kotak Sampah">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- ============================================================== -->
<!-- 4. KOTAK SAMPAH (TRASH SECTION)                                -->
<!-- ============================================================== -->
<div id="trashSection" class="modern-card">
    <div class="modern-card-header">
        <h6 class="modern-card-title text-muted">
            <i class="bi bi-trash3 text-secondary"></i>
            <span>Kotak Sampah (Data Terhapus Sementara)</span>
        </h6>
        @if(!$trashedTodos->isEmpty())
            <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">{{ $trashedTodos->count() }} Tugas</span>
        @endif
    </div>
    <div class="modern-card-body">
        @if($trashedTodos->isEmpty())
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-check-circle text-success fs-5 d-block mb-1"></i>
                Kotak sampah saat ini kosong.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2 text-muted small">Judul Tugas</th>
                            <th class="py-2 text-muted small">Kategori</th>
                            <th class="text-end pe-3 py-2 text-muted small" style="width: 250px;">Aksi Pemulihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashedTodos as $trashed)
                            <tr>
                                <td class="ps-3 py-2.5">
                                    <span class="text-decoration-line-through text-muted fw-semibold small">
                                        {{ $trashed->title }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-clean badge-category">
                                        <i class="bi bi-folder2 text-primary"></i>
                                        {{ $trashed->category->name }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 py-2.5">
                                    <div class="d-inline-flex gap-2">
                                        <form action="{{ route('todos.restore', $trashed->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                                <span>Pulihkan</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('todos.forceDelete', $trashed->id) }}" method="POST" onsubmit="return confirm('Hapus tugas ini secara permanen? Data tidak dapat dipulihkan lagi.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Hapus Permanen">
                                                <i class="bi bi-x-octagon"></i>
                                            </button>
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
@endsection

@push('scripts')
<script>
    // Audio Notification System
    const urgentCount = {{ $urgentCount }};
    function playNotificationSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const context = new AudioContext();
            
            const osc1 = context.createOscillator();
            const gain1 = context.createGain();
            osc1.type = 'sine'; 
            osc1.frequency.setValueAtTime(523.25, context.currentTime); // C5
            gain1.gain.setValueAtTime(0.2, context.currentTime); 
            gain1.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.25);
            osc1.connect(gain1); 
            gain1.connect(context.destination);
            osc1.start(); 
            osc1.stop(context.currentTime + 0.25);

            setTimeout(() => {
                const osc2 = context.createOscillator();
                const gain2 = context.createGain();
                osc2.type = 'sine'; 
                osc2.frequency.setValueAtTime(783.99, context.currentTime); // G5
                gain2.gain.setValueAtTime(0.2, context.currentTime); 
                gain2.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.35);
                osc2.connect(gain2); 
                gain2.connect(context.destination);
                osc2.start(); 
                osc2.stop(context.currentTime + 0.35);
            }, 120);
        } catch (e) {
            console.error("Audio Notification Error: ", e);
        }
    }

    function triggerBrowserNotification(count) {
        if ("Notification" in window && Notification.permission === "granted") {
            new Notification("Pengingat MAZIZ To-Do List", {
                body: `Terdapat ${count} tugas penting yang mendekati atau melewati batas waktu.`,
                icon: 'https://cdn-icons-png.flaticon.com/512/906/906334.png'
            });
        }
    }

    if (urgentCount > 0) {
        if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") triggerBrowserNotification(urgentCount);
            });
        } else {
            triggerBrowserNotification(urgentCount);
        }
    }
</script>
@endpush
