<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MAZIZ To-Do List | Modern Task Management')</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom Modern Stylesheet -->
    <link href="{{ asset('css/maziz-style.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    @php
        $todayDate = date('Y-m-d');
        $urgentBadgeCount = isset($todos) ? $todos->filter(function($t) use ($todayDate) {
            return $t->due_date && $t->due_date <= $todayDate && $t->status != 'completed';
        })->count() : 0;
        $totalTodosBadge = isset($todos) ? $todos->count() : 0;
        $trashedBadgeCount = isset($trashedTodos) ? $trashedTodos->count() : 0;
    @endphp
    <div class="app-wrapper" id="appWrapper">
        <!-- Backdrop untuk Mobile Drawer -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- ============================================================== -->
        <!-- FLOATING SIDEBAR (COLLAPSIBLE & RESPONSIVE)                   -->
        <!-- ============================================================== -->
        <aside class="floating-sidebar" id="floatingSidebar">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <a href="{{ route('todos.index') }}" class="brand-wrapper">
                    <div class="brand-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="brand-info">
                        <span class="brand-title">MAZIZ To-Do</span>
                        <span class="brand-subtitle">Smart Productivity</span>
                    </div>
                </a>
                <button type="button" class="btn btn-sm btn-light border-0 d-lg-none" id="sidebarCloseBtn" aria-label="Tutup Menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="sidebar-body">
                <div class="nav-section-title">Navigasi Utama</div>

                <a href="{{ route('todos.index') }}" class="sidebar-nav-item {{ !request('category_id') && !request('status') ? 'active' : '' }}" title="Semua Tugas">
                    <i class="bi bi-grid-1x2-fill nav-icon"></i>
                    <span class="nav-text">Semua Tugas</span>
                    @if($totalTodosBadge > 0)
                        <span class="badge bg-primary text-white">{{ $totalTodosBadge }}</span>
                    @endif
                </a>

                <a href="#urgentSection" class="sidebar-nav-item" title="Tugas Mendesak">
                    <i class="bi bi-exclamation-triangle-fill nav-icon text-warning"></i>
                    <span class="nav-text">Mendesak</span>
                    @if($urgentBadgeCount > 0)
                        <span class="badge bg-danger text-white">{{ $urgentBadgeCount }}</span>
                    @endif
                </a>

                <a href="#trashSection" class="sidebar-nav-item" title="Kotak Sampah">
                    <i class="bi bi-trash3-fill nav-icon"></i>
                    <span class="nav-text">Kotak Sampah</span>
                    @if($trashedBadgeCount > 0)
                        <span class="badge bg-secondary text-white">{{ $trashedBadgeCount }}</span>
                    @endif
                </a>

                @if(isset($categories) && $categories->count() > 0)
                    <div class="nav-section-title mt-3">Kategori</div>
                    @foreach($categories as $category)
                        <a href="{{ route('todos.index', ['category_id' => $category->id]) }}" 
                           class="sidebar-nav-item {{ request('category_id') == $category->id ? 'active' : '' }}" 
                           title="{{ $category->name }}">
                            <i class="bi bi-tag-fill nav-icon text-primary"></i>
                            <span class="nav-text">{{ $category->name }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Sidebar Footer / Creator Info -->
            <div class="sidebar-footer">
                <div class="sidebar-user-card">
                    <div class="user-avatar">
                        AZ
                    </div>
                    <div class="user-details">
                        <span class="user-name">M. Aziz Farhan</span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ============================================================== -->
        <!-- MAIN CONTENT WRAPPER                                           -->
        <!-- ============================================================== -->
        <div class="main-content-wrapper">
            <!-- Top Navbar Header -->
            <header class="main-top-navbar">
                <div class="navbar-left">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
                        <i class="bi bi-layout-sidebar-inset"></i>
                    </button>
                    <div class="page-headline">
                        <h1 class="page-title">
                            @yield('page_title', 'Daftar Tugas')
                        </h1>
                        <p class="page-subtitle">Kelola seluruh produktivitas harianmu secara rapi dan terorganisir.</p>
                    </div>
                </div>

                <div class="navbar-right">
                    <div class="date-pill">
                        <i class="bi bi-calendar4-week text-primary"></i>
                        <span id="currentDateDisplay">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                    </div>

                    <button type="button" class="btn-modern-primary" data-bs-toggle="modal" data-bs-target="#createTodoModal">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Tugas</span>
                    </button>
                </div>
            </header>

            <!-- Alerts / Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Body Content Yield -->
            <main class="content-body">
                @yield('content')
            </main>

            <!-- ============================================================== -->
            <!-- MINIMALIST FOOTER                                              -->
            <!-- ============================================================== -->
            <footer class="app-footer">
                <p class="footer-copyright m-0 text-center w-100">
                    &copy; 2026 <strong>MAZIZ To-Do List</strong> - Muhammad Aziz Farhan nur Rohim
                </p>
            </footer>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL TAMBAH TUGAS (SLIDE-UP & FADE ANIMATION)                -->
    <!-- ============================================================== -->
    <div class="modal fade" id="createTodoModal" tabindex="-1" aria-labelledby="createTodoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-icon" style="width: 34px; height: 34px; font-size: 1rem; border-radius: 8px;">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <h5 class="modal-title fw-bold" id="createTodoModalLabel">Tambah Tugas Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('todos.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Selesaikan laporan proyek..." required autofocus>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @if(isset($categories))
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Prioritas <span class="text-danger">*</span></label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">Rendah (Low)</option>
                                    <option value="medium" selected>Sedang (Medium)</option>
                                    <option value="high">Tinggi (High)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Batas Waktu (Deadline)</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">Deskripsi / Catatan Tambahan</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan catatan atau instruksi pengerjaan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modern-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-modern-primary">
                            <i class="bi bi-check-lg"></i>
                            <span>Simpan Tugas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SortableJS Library -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <!-- Global App JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const appWrapper = document.getElementById('appWrapper');
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

            // 1. Toggle Sidebar Logic (Smooth Desktop Collapse & Mobile Drawer)
            const isSidebarCollapsed = localStorage.getItem('maziz_sidebar_collapsed') === 'true';
            if (isSidebarCollapsed && window.innerWidth >= 992) {
                appWrapper.classList.add('sidebar-collapsed');
            }

            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', () => {
                    if (window.innerWidth < 992) {
                        // Mode Mobile: Toggle Drawer
                        document.body.classList.toggle('sidebar-mobile-open');
                        sidebarBackdrop.classList.toggle('show');
                    } else {
                        // Mode Desktop: Toggle Collapse
                        appWrapper.classList.toggle('sidebar-collapsed');
                        localStorage.setItem('maziz_sidebar_collapsed', appWrapper.classList.contains('sidebar-collapsed'));
                    }
                });
            }

            // Close Mobile Sidebar when clicking backdrop or close button
            function closeMobileSidebar() {
                document.body.classList.remove('sidebar-mobile-open');
                sidebarBackdrop.classList.remove('show');
            }

            if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);
            if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closeMobileSidebar);

            // 2. Initialize SortableJS with smooth floating micro-animations
            const sortableContainer = document.getElementById('sortableTodoList');
            if (sortableContainer && typeof Sortable !== 'undefined') {
                new Sortable(sortableContainer, {
                    handle: '.drag-handle',
                    animation: 250,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    forceFallback: true,
                    fallbackClass: 'sortable-drag',
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    onEnd: function (evt) {
                        // Event callback when drag finishes (bisa disambung ke AJAX reorder jika diperlukan)
                        console.log('Task reordered from', evt.oldIndex, 'to', evt.newIndex);
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
