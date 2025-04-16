<!doctype html>

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{url('assets')}}/"
    data-template="vertical-menu-template"
    data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title><?= $page ?></title>
    <link rel="icon" type="image/x-icon" href="{{url('assets/img/favicon/favicon.ico')}}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{url('assets/vendor/fonts/tabler-icons.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/rtl/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{url('assets/css/demo.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/node-waves/node-waves.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/typeahead-js/typeahead.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/css/pages/cards-advance.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/select2/select2.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
    <link rel="stylesheet" href="{{url('assets/vendor/libs/tagify/tagify.css')}}" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link rel="stylesheet" href="{{url('assets/css/style.css')}}" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>

    <script src="{{url('assets/vendor/js/helpers.js')}}"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="{{url('assets/js/config.js')}}"></script>
    <script>
        url = '<?= url('') ?>'
    </script>
    <script>
        rol = '<?= $rol ?>'
    </script>
    <script>
        // Permisos para el módulo Sedes
        var canViewSedes = <?= json_encode($user->can('sede.listado.v')); ?>;
        var canAddSedes = <?= json_encode($user->can('sede.sede.a')); ?>;
        var canEditSedes = <?= json_encode($user->can('sede.sede.e')); ?>;
        var canDeleteSedes = <?= json_encode($user->can('sede.sede.d')); ?>;
        var canViewHorarios = <?= json_encode($user->can('sede.horarios.v')); ?>;
        var canAddHorarios = <?= json_encode($user->can('sede.horarios.a')); ?>;
        var canEditHorarios = <?= json_encode($user->can('sede.horarios.e')); ?>;
        var canDeleteHorarios = <?= json_encode($user->can('sede.horarios.d')); ?>;
        var canViewFestivos = <?= json_encode($user->can('sede.dias_festivos.v')); ?>;
        var canAddFestivos = <?= json_encode($user->can('sede.dias_festivos.a')); ?>;
        var canEditFestivos = <?= json_encode($user->can('sede.dias_festivos.e')); ?>;
        var canDeleteFestivos = <?= json_encode($user->can('sede.dias_festivos.d')); ?>;
        var canViewServicios = <?= json_encode($user->can('sede.servicios.v')); ?>;
        var canAddServicios = <?= json_encode($user->can('sede.servicios.a')); ?>;
        var canEditServicios = <?= json_encode($user->can('sede.servicios.e')); ?>;
        var canDeleteServicios = <?= json_encode($user->can('sede.servicios.d')); ?>;

        // Permisos para el módulo Clientes
        var canViewClientes = <?= json_encode($user->can('cliente.listado.v')); ?>;
        var canAddClientes = <?= json_encode($user->can('cliente.Cliente.a')); ?>;
        var canEditClientes = <?= json_encode($user->can('cliente.Cliente.e')); ?>;
        var canDeleteClientes = <?= json_encode($user->can('cliente.Cliente.d')); ?>;
        var canDownloadClientes = <?= json_encode($user->can('cliente.descargar.v')); ?>;

        // Permisos para el módulo Vehículos
        var canViewVehiculos = <?= json_encode($user->can('vehiculo.listado.v')); ?>;
        var canAddVehiculos = <?= json_encode($user->can('vehiculo.Vehiculo.a')); ?>;
        var canEditVehiculos = <?= json_encode($user->can('vehiculo.Vehiculo.e')); ?>;
        var canDeleteVehiculos = <?= json_encode($user->can('vehiculo.Vehiculo.d')); ?>;

        // Permisos para el módulo Citas (usando "cita.Cita.*" para acciones sobre citas)
        var canViewCitas = <?= json_encode($user->can('cita.listado.v') || $user->can('cita.Cita.v')); ?>;
        var canViewSedeCita = <?= json_encode($user->can('cita.Ver sede.v') || $user->can('sede.listado.v')); ?>;
        var canAddCitas = <?= json_encode($user->can('cita.Cita.a')); ?>;
        var canEditCitas = <?= json_encode($user->can('cita.Cita.e')); ?>;
        var canDeleteCitas = <?= json_encode($user->can('cita.Cita.d')); ?>;
        var canViewEstadoCitas = <?= json_encode($user->can('cita.Estado Cita.v')); ?>;
        var canEditEstadoCitas = <?= json_encode($user->can('cita.Estado Cita.e')); ?>;
        var canDeleteEstadoCitas = <?= json_encode($user->can('cita.Estado Cita.d')); ?>;
        var canViewEstadoCitasVerificado = <?= json_encode($user->can('cita.Estado Verificado.v')); ?>;
        var canEditEstadoCitasVerificado = <?= json_encode($user->can('cita.Estado Verificado.e')); ?>;
        var canViwTag = <?= json_encode($user->can('cita.Ver Tag.v')); ?>;
        var canViewOrigen = <?= json_encode($user->can('cita.Ver Origen.v')); ?>;
        var canViewServiciosLiquidador = <?= json_encode($user->can('cita.Servicio Liquidador.v')); ?>;
        var canEditServiciosLiquidador = <?= json_encode($user->can('cita.Servicio Liquidador.e')); ?>;
        var canDeleteServiciosLiquidador = <?= json_encode($user->can('cita.Servicio Liquidador.d')); ?>;
        var canEditCualquierFecha = <?= json_encode($user->can('cita.Seleccionar Cualquier Fecha.e')); ?>;

        // Permisos para ver y editar el agente call center dentro de citas
        var canViewCallCenter = <?= json_encode($user->can('cita.Agente Call Center.v')); ?>;
        var canEditCallCenter = <?= json_encode($user->can('cita.Agente Call Center.e')); ?>;

        // Permisos para el módulo Usuarios
        var canViewUsuarios = <?= json_encode($user->can('usuario.Usuario.v')); ?>;
        var canAddUsuarios = <?= json_encode($user->can('usuario.Usuario.a')); ?>;
        var canEditUsuarios = <?= json_encode($user->can('usuario.Usuario.e')); ?>;
        var canDeleteUsuarios = <?= json_encode($user->can('usuario.Usuario.d')); ?>;

        // Permisos para el módulo Liquidador
        var canViewLiquidador = <?= json_encode($user->can('liquidador.Listado.v')); ?>;
        var canViewPagoMasivo = <?= json_encode($user->can('liquidador.Pago Masivo Realizado.v')); ?>;
        var canEditValidacionOpConfirmado = <?= json_encode($user->can('liquidador.validacion op(confirmado).e')); ?>;
        // Puedes agregar más variables para los permisos de liquidador según lo necesites

        // Permisos para el módulo Estadísticas
        var canViewEstadisticas = <?= json_encode($user->can('estadisticas.panel1.v')); ?>;

        // Permisos para la gestión de Roles
        var canViewRoles = <?= json_encode($user->can('roles.Roles.v')); ?>;
        var canAddRoles = <?= json_encode($user->can('roles.Roles.a')); ?>;
        var canEditRoles = <?= json_encode($user->can('roles.Roles.e')); ?>;
        var canDeleteRoles = <?= json_encode($user->can('roles.Roles.d')); ?>;

        // Permisos para la gestión de Permisos
        var canManagePermissions = <?= json_encode($user->can('permissions.administrar.v')); ?>;
        // Si prefieres separar en ver, crear, editar, eliminar, puedes crear variables similares.
    </script>

</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">