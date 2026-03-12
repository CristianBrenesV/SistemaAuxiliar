<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Página Principal') - Sistema Contable de Desarrollos Ordenados S.A</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Token CSRF necesario para AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { overflow-x: hidden; }
        .sidebar { min-height: 100vh; }
        .topbar { height: 60px; color: white; padding: 0 1rem; }
        .pagination-dark .page-item.active .page-link { background-color: #333; border-color: #333; color: #fff; }
        .pagination-dark .page-link { color: #000; background-color: transparent; border: 1px solid #dee2e6; }
        .pagination-dark .page-link:hover { background-color: #e0e0e0; color: #000; }
        .pagination-dark .page-item.disabled .page-link { color: #6c757d; background-color: #fff; border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 bg-dark sidebar text-white p-3">
                <div class="mb-3 d-flex justify-content-center">
                    <a href="{{ url('/principal') }}">
                        <img src="/images/logo2.png" alt="Icono" style="width: 70px; height: 70px;" class="me-2" />
                    </a>
                </div>
                <div class="mb-3 d-flex justify-content-center">
                    <h4 class="text-center">Desarrollos Ordenados S.A</h4>
                </div>
                <div class="w-80 mx-auto" style="height: 3px; background-color: #e6e6e6; margin-bottom: 5px"></div>

                <ul class="nav flex-column">
                    <li class="nav-item mt-3"><span class="text-white h6">Usuarios</span></li>
                    <a class="nav-link text-white" href="{{ route('usuarios.index') }}"><i class="bi bi-people-fill"></i> Gestión Usuarios</a>

                    <li class="nav-item mt-3"><span class="text-white h6">Centros de Costo</span></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-grid"></i> Gestión Centros de Costo</a></li>

                    <li class="nav-item mt-3"><span class="text-white h6">Terceros</span></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-people"></i> Gestión Terceros</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-geo-alt"></i> Direcciones</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#"><i class="bi bi-person-lines-fill"></i> Contactos</a></li>

                    <li class="nav-item mt-3"><span class="text-white h6">Asignaciones / Prorrateo</span></li>
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->routeIs('asientos.index') ? 'fw-bold bg-primary rounded' : '' }}" 
                        href="{{ route('asientos.index') }}">
                            <i class="bi bi-journal-check"></i> Prorrateo de Asientos
                        </a>
                    </li>

                    <li class="nav-item mt-3"><span class="text-white h6">Reportes</span></li>

                    <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('reportes.centros') ? 'fw-bold bg-primary rounded' : '' }}"
                    href="{{ route('reportes.centros') }}">
                    <i class="bi bi-diagram-3"></i> Movimientos por Centro de Costo
                    </a>
                    </li>

                    <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('reportes.terceros') ? 'fw-bold bg-primary rounded' : '' }}"
                    href="{{ route('reportes.terceros') }}">
                    <i class="bi bi-person-lines-fill"></i> Movimientos por Tercero
                    </a>
                    </li>

                </ul>
            </nav>

            <!-- Main -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="topbar bg-dark d-flex justify-content-end align-items-center mb-3">
                    <!-- Nombre del usuario -->
                    <span class="me-2 text-white">{{ session('user_name', 'Invitado') }}</span>

                    <!-- Avatar como dropdown -->
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="avatarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/images/avatar_generico.jpg" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;" />
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end bg-dark" aria-labelledby="avatarDropdown">
                            <li class="px-1 py-2">
                                <form method="POST" action="{{ url('/logout') }}" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-dark w-100 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <div class="modal fade" id="sessionExpiredModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sesión caducada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tu sesión ha expirado por inactividad. Serás redirigido al login.</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-dark" href="{{ url('/login') }}">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/inactividad.js') }}"></script>
    @yield('scripts')
    @stack('scripts')

</body>
</html>
