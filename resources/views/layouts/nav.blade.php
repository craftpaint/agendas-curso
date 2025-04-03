<script>
    rol = '<?= $rol ?>';
</script>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand  my-3">
        <a href="{{url('')}}" class="app-brand-link pt-5">
            <img src="{{ url('assets/img/logos/logo-curso.svg') }}" alt="Logo" class="img-fluid w-100" style="object-fit: contain;">
            {{-- <span class="app-brand-text demo menu-text fw-bold">CITAS</span> --}}
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-header small" style="padding-top:0">
            <span class="menu-header-text pb-2" style="display: block;color:#000"><?= $user->email ?></span>
            <?php
            if ($user->id_sede !== null) {
                $sede = \App\Helpers\AdminHelper::get_sede_by_id($user->id_sede);
            ?>
                <span class="badge bg-label-dark">Sede: <?= $sede['nombre_sede'] ?></span>
            <?php
            }
            ?>
            <span class="badge bg-label-primary">Rol: <?= $rol ?></span>
        </li>
        <?php
        if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Sedes') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-building"></i>
                    <div>Modulo sedes</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Sedes' && $subpage == 'Listado') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/sedes') }}" class="menu-link">
                            <div>Sedes</div>
                        </a>
                    </li>
                    <li class="menu-item <?= ($page == 'Sedes' && $subpage == 'Configuración') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/sedes/configuracion') }}" class="menu-link">
                            <div>Configuración</div>
                        </a>
                    </li>
                </ul>
            </li>
        <?php
        }
        if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'gestorsede' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Clientes') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-user-heart"></i>
                    <div>Modulo clientes</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Clientes' && $subpage == 'Listado') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/clientes') }}" class="menu-link">
                            <div>Clientes</div>
                        </a>
                    </li>
                    <li class="menu-item <?= ($page == 'Clientes' && $subpage == 'Vehiculos') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/clientes/vehiculos') }}" class="menu-link">
                            <div>Vehiculos</div>
                        </a>
                    </li>
                </ul>
            </li>
        <?php
        }
        if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'callcenter' || $rol == 'gestorsede' || $rol == 'liquidador' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Citas') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-calendar-heart"></i>
                    <div>Modulo citas</div>
                    <?= ($alert > 0) ? '<div class="badge bg-danger rounded-pill ms-auto">' . $alert . '</div>' : '' ?>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Citas' && $subpage == 'Listado') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/citas') }}" class="menu-link">
                            <div>Todas las Citas</div>
                        </a>
                    </li>
                    <li class="menu-item <?= ($page == 'Citas' && $subpage == 'Crear citas') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/citas/add') }}" class="menu-link">
                            <div>Crear citas</div>
                        </a>
                    </li>
                    <?php
                    if ($rol == 'superadmin' || $rol == 'lidercallcenter') {
                    ?>
                        <li class="menu-item <?= ($page == 'Citas' && $subpage == 'Configuración') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/citas/configuracion') }}" class="menu-link">
                                <div>Configuración</div>
                            </a>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </li>
        <?php }
        if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Usuarios') ? 'active' : '' ?>">
                <a href="{{ url('dashboard/usuarios') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div>Usuarios</div>
                </a>
            </li>
        <?php
        }
        if ($rol == 'superadmin' || $rol == 'liquidador' || $rol == 'admin' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Liquidador') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-clipboard"></i>
                    <div>Liquidador</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Liquidador' && $subpage == 'Listado') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/liquidador') }}" class="menu-link">
                            <div>Todas las Citas</div>
                        </a>
                    </li>
                </ul>
            </li>
        <?php
        }
        if ($rol == 'superadmin' || $rol == 'admin' || $rol == 'lidercallcenter') {
        ?>
            <li class="menu-item <?= ($page == 'Estadisticas') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                    <div>Estadisticas</div>
                    <?= ($alert > 0) ? '<div class="badge bg-danger rounded-pill ms-auto">' . $alert . '</div>' : '' ?>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Estadisticas' && $subpage == 'Global') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/estadisticas') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                            <div>Fechas - citas</div>
                        </a>
                    </li>
                </ul>
                <!-- <ul class="menu-sub">
                    <li class="menu-item <?= ($page == 'Estadisticas' && $subpage == 'sedes') ? 'active' : '' ?>">
                        <a href="{{ url('dashboard/estadisticas/sedes') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                            <div>Sedes</div>
                        </a>
                    </li>
                </ul> -->
            </li>
        <?php
        }
        ?>
        <li class="menu-item">
            <a href="{{ url('dashboard/usuarios/logout') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-logout"></i>
                <div>Cerrar sesión</div>
            </a>
        </li>

    </ul>
</aside>