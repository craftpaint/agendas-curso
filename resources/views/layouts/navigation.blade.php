<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="container-fluid">
        <div class="navbar-nav align-items-center">
            <div class="nav-item d-flex align-items-center">
                <!-- Botón corregido para colapsar el menú -->
                <a href="javascript:void(0);" class="nav-link layout-menu-toggle">
                    <i class="ti ti-menu-2 me-2"></i>
                </a>
            </div>
        </div>

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ substr($user->name, 0, 1) }}
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-medium d-block">{{ $user->name }}</span>
                                        <small class="text-muted">{{ $rol }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('dashboard/usuarios/logout') }}">
                                <i class="ti ti-logout me-2 ti-sm"></i>
                                <span class="align-middle">Cerrar sesión</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>
    </div>
</nav>