<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dashboard\SedesController;
use App\Http\Controllers\Dashboard\ClientesController;
use App\Http\Controllers\Dashboard\CitasController;
use App\Http\Controllers\Dashboard\UsersController;
use App\Http\Controllers\Cron\AlertController;
use App\Http\Controllers\Dashboard\EstadisticasController;
use App\Http\Controllers\Dashboard\RolesController;
use App\Http\Controllers\Dashboard\PermissionsController;
use App\Http\Controllers\Dashboard\EmpresasController;
use App\Http\Controllers\Dashboard\PaquetesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la aplicación. Se utiliza el middleware
| "permission:" con la nomenclatura definida en tus permisos. Asegúrate de que
| en la base estén creados dichos permisos.
|
*/

// Redirecciona la raíz al login
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard: Se redirige usando el permiso para listar citas ("cita.listado.v")
Route::get('/dashboard', function () {
    if (Auth::user()) {
        if (Auth::user()->can('cita.listado.v')) {
            return redirect('/dashboard/citas');
        } else if (Auth::user()->can('liquidador.Listado.v')) {
            return redirect('/dashboard/liquidador');
        }
    } else {
        return redirect('/login');
    }
})->name('dashboard');

// Load
Route::controller(LoadController::class)->group(function () {
    Route::get('load', 'index');
    Route::get('create-cita/{id}', 'createcita');
    Route::post('get-horarios', 'gethorarios');
    Route::post('savecita', 'savecita');
    Route::post('get-servicio-by-id-sede', 'get_servicio_by_id_sede');
    Route::post('get-citas-agendadas', 'getCitasAgendadas');
    Route::post('verificar-cupos-horario', 'postVerificarCuposHorario');
})->name('load');

// Estadísticas (se utiliza "estadisticas.panel1.v" como permiso de visualización global)
Route::controller(EstadisticasController::class)->group(function () {
    // Listado de estadísticas: Globales
    Route::get('dashboard/estadisticas', 'index')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel1.v']);
    Route::get('dashboard/estadisticas/citas', 'getCitasData')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel1.v']);
    Route::get('dashboard/estadisticas/creaciones', 'getCreacionesData')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel1.v']);
    Route::get('dashboard/estadisticas/estado/comparativa', 'getComparativaEstado')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel1.v']);
    Route::get('dashboard/estadisticas/estado-verificado/comparativa', 'getComparativaEstadoVerificado')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel1.v']);




    // Listado de estadísticas: por agentes
    Route::get('dashboard/estadisticas/agentes', 'estadisticasAgentes')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel2.v']);
    Route::get('dashboard/estadisticas/agentes/getStatsPorEstadoAgentes', 'getStatsPorEstadoAgentes')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel2.v']);
    // Endpoint para gráfico de citas agendadas vs atendidas (global y por agente)
    Route::get('dashboard/estadisticas/agentes/citasAtendidas', 'getCitasAtendidasPorDia')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel2.v']);

    // Listado de estadísticas: por sedes
    Route::get('dashboard/estadisticas/sedes', 'estadisticasSedes')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel3.v']);
    Route::get('dashboard/estadisticas/sedes/getStatsPorSede', 'getStatsPorSede')
        ->middleware(['auth', 'verified', 'permission:estadisticas.panel3.v']);
})->name('estadisticas');

// Sedes
Route::controller(SedesController::class)->group(function () {
    // Listado de sedes: "sede.listado.v"
    Route::get('dashboard/sedes', 'index')
        ->middleware(['auth', 'verified', 'permission:sede.listado.v'])
        ->name('sedes.index');
    Route::post('dashboard/sedes/get_sedes', 'get_sedes')
        ->middleware(['auth', 'verified', 'permission:sede.listado.v|cita.Ver sede.v']);
    // Agregar sede: "sede.sede.a"
    Route::get('dashboard/sedes/add', 'add')
        ->middleware(['auth', 'verified', 'permission:sede.sede.a'])
        ->name('sedes.add');
    Route::post('dashboard/sedes/save', 'save')
        ->middleware(['auth', 'verified', 'permission:sede.sede.a']);
    // Editar sede: "sede.sede.e"
    Route::get('dashboard/sedes/view/{id}', 'edit')
        ->middleware(['auth', 'verified', 'permission:sede.sede.e'])
        ->name('sedes.view');
    Route::get('dashboard/sedes/edit/{id}', 'edit')
        ->middleware(['auth', 'verified', 'permission:sede.sede.e'])
        ->name('sedes.edit');
    Route::post('dashboard/sedes/update', 'update')
        ->middleware(['auth', 'verified', 'permission:sede.sede.e']);
    // Eliminar sede: "sede.sede.d"
    Route::post('dashboard/sedes/delete_sede', 'delete_sede')
        ->middleware(['auth', 'verified', 'permission:sede.sede.d']);
    // Configuración: "sede.configuracion.v"
    Route::get('dashboard/sedes/configuracion', 'configuracion')
        ->middleware(['auth', 'verified', 'permission:sede.configuracion.v']);
    // Horarios
    Route::post('dashboard/sedes/get_horarios', 'get_horarios')
        ->middleware(['auth', 'verified', 'permission:sede.horarios.v']);
    Route::post('dashboard/sedes/add_horarios', 'add_horarios')
        ->middleware(['auth', 'verified', 'permission:sede.horarios.a']);
    Route::post('dashboard/sedes/delete_horario', 'delete_horario')
        ->middleware(['auth', 'verified', 'permission:sede.horarios.d']);
    // Días festivos
    Route::post('dashboard/sedes/get_festivos', 'get_festivos')
        ->middleware(['auth', 'verified', 'permission:sede.dias_festivos.v']);
    Route::post('dashboard/sedes/add_festivos', 'add_festivos')
        ->middleware(['auth', 'verified', 'permission:sede.dias_festivos.a']);
    Route::post('dashboard/sedes/delete_festivos', 'delete_festivos')
        ->middleware(['auth', 'verified', 'permission:sede.dias_festivos.d']);
    // Servicios
    Route::post('dashboard/sedes/get_servicio', 'get_servicio')
        ->middleware(['auth', 'verified', 'permission:sede.servicios.v']);
    Route::post('dashboard/sedes/add_servicio', 'add_servicio')
        ->middleware(['auth', 'verified', 'permission:sede.servicios.a']);
    Route::post('dashboard/sedes/delete_servicio', 'delete_servicio')
        ->middleware(['auth', 'verified', 'permission:sede.servicios.d']);
    Route::post('dashboard/sedes/get_servicio_by_id_sede', 'get_servicio_by_id_sede');

    // Rutas para ciudades
    Route::post('dashboard/sedes/get_ciudades', 'get_ciudades')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.v']);
    Route::post('dashboard/sedes/add_ciudad', 'add_ciudad')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.a']);
    Route::post('dashboard/sedes/change_estado_ciudad', 'change_estado_ciudad')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.d']);
    Route::post('dashboard/sedes/get_ciudad', 'get_ciudad')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.e']);
    Route::post('dashboard/sedes/edit_ciudad', 'edit_ciudad')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.e']);
    Route::post('dashboard/sedes/save_ciudad', 'save_ciudad')
        ->middleware(['auth', 'verified', 'permission:sede.Ciudades.e']);
})->name('sedes');

// Clientes
Route::controller(ClientesController::class)->group(function () {
    // Listado de clientes: "cliente.listado.v"
    Route::get('dashboard/clientes', 'index')
        ->middleware(['auth', 'verified', 'permission:cliente.listado.v'])
        ->name('clientes.index');
    Route::post('dashboard/clientes/get_clientes', 'get_clientes')
        ->middleware(['auth', 'verified', 'permission:cliente.listado.v']);
    // Agregar cliente: "cliente.Cliente.a"
    Route::get('dashboard/clientes/add', 'add')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.a'])
        ->name('clientes.add');
    Route::post('dashboard/clientes/save', 'save')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.a']);
    // Ver cliente: "cliente.Cliente.v"
    Route::get('dashboard/clientes/view/{id}', 'view')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.v'])
        ->name('clientes.view');
    // Editar cliente: "cliente.Cliente.e"
    Route::get('dashboard/clientes/edit/{id}', 'edit')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.e'])
        ->name('clientes.edit');
    Route::post('dashboard/clientes/update', 'update')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.e']);
    // Eliminar cliente: "cliente.Cliente.d"
    Route::post('dashboard/clientes/delete_cliente', 'delete_cliente')
        ->middleware(['auth', 'verified', 'permission:cliente.Cliente.d']);
    // Vehículos (Listado de vehículos: "vehiculo.listado.v")
    Route::get('dashboard/clientes/vehiculos', 'vehiculos')
        ->middleware(['auth', 'verified', 'permission:vehiculo.listado.v'])
        ->name('clientes.vehiculos');
    Route::post('dashboard/clientes/get_vehiculos', 'get_vehiculos')
        ->middleware(['auth', 'verified', 'permission:vehiculo.listado.v']);
    // ver vehículo: "vehiculo.Vehiculo.v"
    Route::get('dashboard/clientes/get_vehiculo/{id}', 'view_vehiculos')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.v']);
    // Agregar vehículo: "vehiculo.Vehiculo.a"
    Route::get('dashboard/clientes/add_vehiculos', 'add_vehiculos')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.a'])
        ->name('clientes.add_vehiculos');
    Route::post('dashboard/clientes/save_vehiculo', 'save_vehiculo')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.a']);
    // Editar vehículo: "vehiculo.Vehiculo.e"
    Route::get('dashboard/clientes/edit_vehiculos/{id}', 'edit_vehiculos')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.e'])
        ->name('clientes.edit_vehiculos');
    Route::post('dashboard/clientes/update_vehiculo', 'update_vehiculo')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.e']);
    // Eliminar vehículo: "vehiculo.Vehiculo.d"
    Route::post('dashboard/clientes/delete_vehiculo', 'delete_vehiculo')
        ->middleware(['auth', 'verified', 'permission:vehiculo.Vehiculo.d']);
    // Descargar clientes: "cliente.descargar.v"
    Route::post('dashboard/clientes/dowload', 'dowload')
        ->middleware(['auth', 'verified', 'permission:cliente.descargar.v']);

    Route::get('dashboard/clientes/get_clientes_in_vehicle', 'get_clientes_in_vehicle');
    Route::post('dashboard/clientes/get_vehiculos_by_id_cliente', 'get_vehiculos_by_id_cliente');
})->name('clientes');

// CITAS
Route::controller(CitasController::class)->group(function () {
    // Listado de citas: "cita.listado.v"
    Route::get('dashboard/citas', 'index')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v'])
        ->name('citas.index');
    Route::post('dashboard/citas/get_citas', 'get_citas')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    // Agregar cita: "cita.Cita.a"
    Route::get('dashboard/citas/add', 'add')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.a'])
        ->name('citas.add');
    Route::post('dashboard/citas/save', 'save')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.a']);
    // Horarios: se usa "cita.listado.v" para obtenerlos
    Route::post('dashboard/citas/get_horarios', 'get_horarios');
    // Editar cita: "cita.Cita.e"
    Route::get('dashboard/citas/edit/{id}', 'edit')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.e'])
        ->name('citas.edit');
    // Ver cita: "cita.Cita.v"
    Route::get('dashboard/citas/view/{id}', 'view')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.v'])
        ->name('citas.view');
    // Actualizar cita: "cita.Cita.e"
    Route::post('dashboard/citas/update', 'update')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.e']);
    // Eliminar cita: "cita.Cita.d"
    Route::post('dashboard/citas/delete', 'delete')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.d']);
    // Configuración de citas: "cita.Configuracion.v"
    Route::get('dashboard/citas/configuracion', 'configuracion')
        ->middleware(['auth', 'verified', 'permission:cita.Configuracion.v'])
        ->name('citas.configuracion');
    // Estados para cita (usar "cita.Estado Cita.*")
    Route::post('dashboard/citas/get_estados', 'get_estados')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Cita.v']);
    Route::post('dashboard/citas/add_estados', 'add_estados')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Cita.a']);
    Route::get('dashboard/citas/edit_estados/{id}', 'edit_estados')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Cita.e']);
    Route::post('dashboard/citas/update_estados', 'update_estados')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Cita.e']);
    Route::post('dashboard/citas/delete_estados', 'delete_estados')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Cita.d']);
    // Cambiar estado: "cita.estado.e" para cambios de estado
    Route::post('dashboard/citas/change_estado', 'change_estado')
        ->middleware(['auth', 'verified', 'permission:cita.estado.e']);
    // Cambiar estado verificado: "cita.Estado Verificado.e"
    Route::post('dashboard/citas/change_estado_verificado', 'change_estado_verificado')
        ->middleware(['auth', 'verified', 'permission:cita.Estado Verificado.e']);
    // Cambiar agente call center: "cita.Agente Call Center.e"
    Route::post('dashboard/citas/change_agente_call', 'change_agente_call')
        ->middleware(['auth', 'verified', 'permission:cita.Agente Call Center.e']);
    // Descargar citas: reutilizamos "cita.listado.v"
    Route::post('dashboard/citas/dowload', 'dowload')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    // Otros endpoints para seguimiento, etc.
    Route::post('dashboard/citas/get_seguimiento_cita', 'get_seguimiento_cita')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::post('dashboard/citas/get_informacion_simit', 'get_informacion_simit')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::post('dashboard/citas/get_seguimiento_cita_con_actualizacion', 'get_seguimiento_cita_con_actualizacion')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::post('dashboard/citas/save_seguimiento', 'save_seguimiento')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::get('dashboard/citas/get_new_records', 'get_new_records')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::get('dashboard/citas/get_estado_metodo_scraping', 'get_estado_metodo_scraping')
        ->middleware(['auth', 'verified', 'permission:cita.Metodo scraping.e']);
    Route::post('dashboard/citas/metodo_scraping', 'metodo_scraping')
        ->middleware(['auth', 'verified', 'permission:cita.Metodo scraping.e']);
})->name('citas');

// Usuarios
Route::controller(UsersController::class)->group(function () {
    // Listado de usuarios: "usuario.Usuario.v"
    Route::get('dashboard/usuarios', 'index')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.v'])
        ->name('usuarios.index');
    Route::post('dashboard/usuarios/get', 'get')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.v']);
    // Agregar usuario: "usuario.usuario.a"
    Route::post('dashboard/usuarios/save', 'save')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.a']);
    // Editar usuario: "usuario.Usuario.e"
    Route::post('dashboard/usuarios/update', 'update')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.e']);
    // Eliminar usuario: "usuario.Usuario.d"
    Route::post('dashboard/usuarios/delete', 'delete')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.d']);
    Route::post('dashboard/usuarios/get_user', 'get_user')
        ->middleware(['auth', 'verified', 'permission:usuario.Usuario.v']);
    Route::get('dashboard/usuarios/logout', 'logout');
})->name('usuarios');

// CRON
Route::controller(AlertController::class)->group(function () {
    Route::get('sincronizaralert', 'index');
})->name('sincronizaralert');

// LIQUIDADOR
Route::controller(CitasController::class)->group(function () {
    // Para el listado de liquidador, podemos usar "liquidador.Listado.v" (o definir uno específico para liquidador)
    Route::get('dashboard/liquidador', 'indexLiquidador')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v'])
        ->name('liquidador.index');
    Route::get('dashboard/liquidador/cda', 'indexLiquidadorCDA')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v']);
    Route::get('dashboard/liquidador/cea', 'indexLiquidadorCEA')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v']);
    Route::get('dashboard/liquidador/cia', 'indexLiquidadorCIA')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v']);
    Route::get('dashboard/liquidador/crc', 'indexLiquidadorCRC')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v']);
    Route::post('dashboard/liquidador/get_citas', 'get_citas_liquidador')
        ->middleware(['auth', 'verified', 'permission:liquidador.Listado.v']);
    // Para guardar (crear) cita en liquidador, se puede usar "cita.Cita.a" o asignar uno específico, aquí usamos "cita.Cita.a"
    Route::post('dashboard/liquidador/save', 'save')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.a']);
    Route::post('dashboard/liquidador/get_horarios', 'get_horarios')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
    Route::get('dashboard/liquidador/edit/{id}', 'edit')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.e']);
    Route::get('dashboard/liquidador/view/{id}', 'view')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.v']);
    Route::post('dashboard/liquidador/update', 'update')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.e']);
    Route::post('dashboard/liquidador/delete', 'delete')
        ->middleware(['auth', 'verified', 'permission:cita.Cita.d']);
    Route::post('dashboard/liquidador/get_servicio_liquidador', 'get_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.v']);
    Route::post('dashboard/liquidador/add_servicio_liquidador', 'add_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.a']);
    Route::get('dashboard/liquidador/edit_servicio_liquidador/{id}', 'edit_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.e']);
    Route::post('dashboard/liquidador/update_servicio_liquidador', 'update_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.e']);
    Route::post('dashboard/liquidador/change_servicio_liquidador', 'change_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.e']);
    Route::post('dashboard/liquidador/change_estado_servicio_liquidador', 'change_estado_servicio_liquidador')
        ->middleware(['auth', 'verified', 'permission:cita.Servicio Liquidador.e']);
    Route::post('dashboard/liquidador/updateComentario', 'updateComentario')
        ->middleware(['auth', 'verified', 'permission:cita.Anotaciones.e']);
    Route::post('dashboard/liquidador/updatePagoMasivo', 'updatePagoMasivo')
        ->middleware(['auth', 'verified', 'permission:liquidador.Pago Realizado.e']);
    Route::post('dashboard/liquidador/dowloadLiquidador', 'dowloadLiquidador')
        ->middleware(['auth', 'verified', 'permission:cita.listado.v']);
})->name('liquidador');

// Roles (Gestión de Roles)
Route::middleware(['auth', 'verified', 'permission:roles.Roles.v'])->group(function () {
    Route::controller(RolesController::class)->group(function () {
        Route::get('dashboard/roles', 'index')
            ->name('roles.index');
        Route::get('dashboard/roles/create', 'create')
            ->middleware('permission:roles.Roles.a')
            ->name('roles.create');
        Route::post('dashboard/roles/store', 'store')
            ->middleware('permission:roles.Roles.a')
            ->name('roles.store');
        Route::get('dashboard/roles/edit/{role}', 'edit')
            ->middleware('permission:roles.Roles.e')
            ->name('roles.edit');
        Route::post('dashboard/roles/update/{role}', 'update')
            ->middleware('permission:roles.Roles.e')
            ->name('roles.update');
        Route::delete('dashboard/roles/destroy/{role}', 'destroy')
            ->middleware('permission:roles.Roles.d')
            ->name('roles.destroy');
    });
});

// Permisos (Gestión de Permisos)
Route::middleware(['auth', 'verified', 'permission:permissions.administrar.v'])->group(function () {
    Route::controller(PermissionsController::class)->group(function () {
        Route::get('dashboard/configuracion/permissions', 'index')->name('permissions.index');
        Route::get('dashboard/configuracion/permissions/create', 'create')
            ->middleware('permission:permissions.administrar.a')
            ->name('permissions.create');
        Route::post('dashboard/configuracion/permissions/store', 'store')
            ->middleware('permission:permissions.administrar.a')
            ->name('permissions.store');
        Route::get('dashboard/configuracion/permissions/edit/{permission}', 'edit')
            ->middleware('permission:permissions.administrar.e')
            ->name('permissions.edit');
        Route::post('dashboard/configuracion/permissions/update/{permission}', 'update')
            ->middleware('permission:permissions.administrar.e')
            ->name('permissions.update');
        Route::delete('dashboard/configuracion/permissions/destroy/{permission}', 'destroy')
            ->middleware('permission:permissions.administrar.d')
            ->name('permissions.destroy');
    });
});

// Grupo de rutas para Empresas
Route::middleware(['auth', 'verified', 'permission:empresa.listado.v'])->group(function () {
    // Listado de empresas: se requiere el permiso para ver listado de empresas
    Route::get('dashboard/empresas', [EmpresasController::class, 'index'])->name('empresas.index');
});

// Otras operaciones que requieren permisos distintos:
Route::middleware(['auth', 'verified', 'permission:empresa.Empresa.a'])->group(function () {
    Route::post('dashboard/empresas/guardar', [EmpresasController::class, 'guardarEmpresa'])->name('empresas.guardarEmpresa');
});

Route::middleware(['auth', 'verified', 'permission:empresa.Empresa.e'])->group(function () {
    Route::post('dashboard/empresas/actualizar/{id}', [EmpresasController::class, 'actualizar'])->name('empresas.actualizar');
});

Route::middleware(['auth', 'verified', 'permission:empresa.Empresa.d'])->group(function () {
    Route::post('dashboard/empresas/cambiar_estado/', [EmpresasController::class, 'cambiarEstado'])->name('empresas.cambiarEstado');
});

Route::middleware(['auth', 'verified', 'permission:empresa.Empresa.a'])
    ->post('dashboard/empresas/guardar-logo', [EmpresasController::class, 'guardarLogo'])->name('empresas.guardarLogo');



Route::controller(EmpresasController::class)->group(function () {
    Route::get('dashboard/empresa', 'obtenerDashboardEmpresa')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::get('dashboard/empresa/consultar_todas_empresas/', 'consultarTodasEmpresas')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::get('dashboard/empresa/consultar_empresa/{id}', 'consultarEmpresa')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::get('dashboard/empresa/consultar_empresa_por_sede/{id}', 'consultarEmpresaPorSede')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::post('dashboard/empresa/obtener_datos_barra_progreso', 'obtenerDatosProgressBarDasboardEmpresa')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::post('dashboard/empresa/obtener_datos_linea_mezclada', 'obtenerDatosLineBarMixedDashboardEmpresa')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::post('dashboard/empresa/obtener_datos_historial_paquetes', 'obtenerDatosHistorialPaquetes')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::post('dashboard/empresa/obtener_datos_paquetes_pendientes', 'obtenerDatosPaquetesPendientes')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
    Route::get('dashboard/empresa/consultar_citas_empresa_paquete/{id_empresa_paquete}', 'consultarCitasEmpresaPaquete')
        ->middleware(['auth', 'verified', 'permission:empresa.dashboard.v']);
});

Route::controller(EmpresasController::class)->group(function () {
    Route::post('webhook/wompi', 'handleWebhook');
});

// Rutas para Paquetes
Route::controller(PaquetesController::class)->group(function () {
    Route::get('dashboard/paquetes', 'index')
        ->middleware(['auth', 'verified', 'permission:paquete.listado.v'])
        ->name('paquetes.index');
    Route::post('dashboard/paquetes/obtener_paquetes', 'obtenerPaquetes')
        ->middleware(['auth', 'verified', 'permission:paquete.listado.v']);
    Route::post('dashboard/paquetes/cambio_estado', 'cambioEstadoPaquete')
        ->middleware(['auth', 'verified', 'permission:paquete.listado.d']);
    Route::post('dashboard/paquetes/guardar', 'guardarPaquete')
        ->middleware(['auth', 'verified', 'permission:paquete.listado.a']);
    Route::post('dashboard/paquetes/actualizar/{id}', 'actualizarPaquete')
        ->middleware(['auth', 'verified', 'permission:paquete.listado.e']);
})->name('paquetes');

require __DIR__ . '/auth.php';

