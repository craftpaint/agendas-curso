<script>
    rol = '<?= $rol ?>';
</script>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand my-3">
        <a href="{{ url('') }}" class="app-brand-link pt-5">
            <img src="{{ url('assets/img/logos/logo-curso.svg') }}" alt="Logo" class="img-fluid w-100" style="object-fit: contain;">
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-header small" style="padding-top:0">
            <span class="menu-header-text pb-2" style="display: block; color:#000"><?= $user->email ?></span>
            <?php
            if ($user->id_sede !== null) {
                $sede = \App\Helpers\AdminHelper::get_sede_by_id($user->id_sede);
            ?>
                <span class="badge bg-label-dark">Sede: <?= $sede['nombre_sede'] ?></span>
            <?php } ?>
            <span class="badge bg-label-primary">Rol: <?= $rol ?></span>
        </li>

        <?php
        // Módulo sedes: se muestra si el usuario tiene permiso para ver el listado de sedes (sede.listado.v)
        if (auth()->user()->can('sede.listado.v')) {
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
                    <?php if (auth()->user()->can('sede.configuracion.v')) { ?>
                        <li class="menu-item <?= ($page == 'Sedes' && $subpage == 'Configuración') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/sedes/configuracion') }}" class="menu-link">
                                <div>Configuración</div>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </li>
        <?php
        }
        // Módulo clientes: se muestra si el usuario tiene permiso para ver clientes (cliente.listado.v)
        if ($user->can('cliente.listado.v')) {
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
        // Módulo citas: se muestra si el usuario tiene permiso para ver el listado de citas (cita.listado.v o cita.Cita.v)
        if ($user->can('cita.listado.v') || $user->can('cita.Cita.v')) {
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
                    <?php if (auth()->user()->can('cita.Cita.a')) { ?>
                        <li class="menu-item <?= ($page == 'Citas' && $subpage == 'Crear citas') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/citas/add') }}" class="menu-link">
                                <div>Crear citas</div>
                            </a>
                        </li>
                    <?php }
                    // Se muestran opciones de configuración sólo si el usuario tiene el permiso específico para configuración de citas.
                    if ($user->can('cita.Configuracion.v')) {
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
        <?php
        }
        // Módulo usuarios: se muestra si el usuario tiene permiso para ver usuarios ("usuario.Usuario.v")
        if ($user->can('usuario.Usuario.v')) {
        ?>
            <li class="menu-item <?= ($page == 'Usuarios') ? 'active' : '' ?>">
                <a href="{{ url('dashboard/usuarios') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div>Usuarios</div>
                </a>
            </li>
        <?php
        }
        // Módulo liquidador: se muestra si el usuario tiene el permiso para ver citas (ej. "cita.listado.v") o, mejor, si existe un permiso específico para liquidador.
        // En este ejemplo, usaré "liquidador.Listado.v" si está definido.
        if ($user->can('liquidador.Listado.v')) {
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
        // Módulo estadísticas: se muestra si el usuario tiene permiso para ver el panel de estadísticas (ej. "estadisticas.panel1.v")
        if ($user->can('estadisticas.panel1.v') || $user->can('estadisticas.panel2.v') || $user->can('estadisticas.panel3.v')) {
        ?>
            <li class="menu-item <?= ($page == 'Estadisticas') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                    <div>Estadisticas</div>
                    <?= ($alert > 0) ? '<div class="badge bg-danger rounded-pill ms-auto">' . $alert . '</div>' : '' ?>
                </a>
                <ul class="menu-sub">
                    <?php if ($user->can('estadisticas.panel1.v')) { ?>
                        <li class="menu-item <?= ($page == 'Estadisticas' && $subpage == 'Global') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/estadisticas') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                                <div>Fechas - citas</div>
                            </a>
                        </li>
                    <?php
                    }
                    if ($user->can('estadisticas.panel2.v')) {
                    ?>
                        <li class="menu-item <?= ($page == 'Estadisticas' && $subpage == 'Agentes') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/estadisticas/agentes') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                                <?php if ($user->can('estadisticas.Solo ver estadisticas propias.v')) { ?>
                                    <div>Mis estadísticas</div>
                                <?php } else { ?>
                                    <div>Agentes</div>
                                <?php } ?>
                            </a>
                        </li>
                    <?php
                    }
                    if ($user->can('estadisticas.panel3.v')) {
                    ?>
                        <li class="menu-item <?= ($page == 'Estadisticas' && $subpage == 'Sedes') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/estadisticas/sedes') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-chart-histogram"></i>
                                <?php if ($user->can('estadisticas.Solo ver estadisticas propias.v')) { ?>
                                    <div>Como va la sede</div>
                                <?php } else { ?>
                                    <div>Sedes</div>
                                <?php } ?>
                            </a>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </li>
        <?php
        }
        // Módulo configuración (Roles y Permisos): se muestra solo para usuarios que tengan permisos para gestionar roles y permisos.
        if ($user->can('roles.Roles.v') || $user->can('permissions.administrar.v')) {
        ?>
            <li class="menu-item <?= ($page == 'Configuracion') ? 'active open' : '' ?>">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i>
                    <div>Configuración</div>
                </a>
                <ul class="menu-sub">
                    <?php if ($user->can('roles.Roles.v')) { ?>
                        <li class="menu-item <?= ($page == 'Configuracion' && $subpage == 'Roles') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/roles') }}" class="menu-link">
                                <div>Roles</div>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($user->can('permissions.administrar.v')) { ?>
                        <li class="menu-item <?= ($page == 'Configuracion' && $subpage == 'Permisos') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/configuracion/permissions') }}" class="menu-link">
                                <div>Permisos</div>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if ($user->can('empresa.listado.v')) { ?>
                        <li class="menu-item <?= ($page == 'Configuracion' && $subpage == 'Empresa') ? 'active' : '' ?>">
                            <a href="{{ url('dashboard/empresas') }}" class="menu-link">
                                <div>Empresas</div>
                            </a>
                        </li>
                    <?php } ?>

                </ul>
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