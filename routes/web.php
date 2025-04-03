<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\SedesController;
use App\Http\Controllers\Dashboard\ClientesController;
use App\Http\Controllers\Dashboard\CitasController;
use App\Http\Controllers\Dashboard\UsersController;
use App\Http\Controllers\Cron\AlertController;
use App\Http\Controllers\Dashboard\EstadisticasController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/dashboard', function () {
    $rol = Auth::user()->getRoleNames()->first();
    if ($rol == 'superadmin' || $rol == 'admin') {
        return redirect('/dashboard/citas');
    } else if ($rol == 'liquidador') {
        return redirect('/dashboard/liquidador');
    } else {
        return redirect('/dashboard/citas');
    }
})->name('dashboard');

//Load
Route::controller(LoadController::class)->group(function () {
    Route::get('load', 'index');
    Route::get('create-cita/{id}', 'createcita');
    Route::post('get-horarios', 'gethorarios');
    Route::post('savecita', 'savecita');
    route::post('get-servicio-by-id-sede', 'get_servicio_by_id_sede');
})->name('load');

//estadisticas
Route::controller(EstadisticasController::class)->group(function () {
    Route::get('dashboard/estadisticas/citas', 'getCitasData')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::get('dashboard/estadisticas/creaciones', 'getCreacionesData')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    route::get('dashboard/estadisticas', 'index')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    route::get('dashboard/estadisticas/sedes', 'viewSedes')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
})->name('Estadisticas');

//SEDES
Route::controller(SedesController::class)->group(function () {
    Route::get('dashboard/sedes', 'index')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/get_sedes', 'get_sedes')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::get('dashboard/sedes/add', 'add')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/save', 'save')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::get('dashboard/sedes/edit/{id}', 'edit')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/update', 'update')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/delete_sede', 'delete_sede')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::get('dashboard/sedes/configuracion', 'configuracion')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/get_horarios', 'get_horarios')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/add_horarios', 'add_horarios')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/delete_horario', 'delete_horario')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/get_festivos', 'get_festivos')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/add_festivos', 'add_festivos')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/delete_festivos', 'delete_festivos')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/get_servicio', 'get_servicio')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/get_servicio_by_id_sede', 'get_servicio_by_id_sede')->middleware(['auth', 'verified', 'role:admin|superadmin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/sedes/add_servicio', 'add_servicio')->middleware(['auth', 'verified', 'role:admin|superadmin|lidercallcenter']);
    Route::post('dashboard/sedes/delete_servicio', 'delete_servicio')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
})->name('sedes');
// CLIENTES
Route::controller(ClientesController::class)->group(function () {
    Route::get('dashboard/clientes', 'index')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/clientes/get_clientes', 'get_clientes')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/add', 'add')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/clientes/save', 'save')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/view/{id}', 'view')->middleware(['auth', 'verified', 'role:gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/edit/{id}', 'edit')->middleware(['auth', 'verified', 'role:superadmin|callcenter|lidercallcenter']);
    Route::post('dashboard/clientes/update', 'update')->middleware(['auth', 'verified', 'role:superadmin|callcenter|lidercallcenter']);
    Route::post('dashboard/clientes/delete_cliente', 'delete_cliente')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::get('dashboard/clientes/vehiculos', 'vehiculos')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/clientes/get_vehiculos', 'get_vehiculos')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/add_vehiculos', 'add_vehiculos')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/clientes/save_vehiculo', 'save_vehiculo')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/get_clientes_in_vehicle', 'get_clientes_in_vehicle')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/clientes/get_vehiculos_by_id_cliente', 'get_vehiculos_by_id_cliente')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/view_vehiculos/{id}', 'view_vehiculos')->middleware(['auth', 'verified', 'role:gestorsede|lidercallcenter']);
    Route::get('dashboard/clientes/edit_vehiculos/{id}', 'edit_vehiculos')->middleware(['auth', 'verified', 'role:superadmin|callcenter|lidercallcenter']);
    Route::post('dashboard/clientes/update_vehiculo', 'update_vehiculo')->middleware(['auth', 'verified', 'role:superadmin|callcenter|lidercallcenter']);
    Route::post('dashboard/clientes/delete_vehiculo', 'delete_vehiculo')->middleware(['auth', 'verified', 'role:superadmin|lidercallcenter']);
    Route::post('dashboard/clientes/dowload', 'dowload')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
})->name('clientes');
// CITAS
Route::controller(CitasController::class)->group(function () {
    Route::get('dashboard/citas', 'index')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::get('dashboard/citas/cda', 'indexCDA')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::get('dashboard/citas/cea', 'indexCEA')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::get('dashboard/citas/cia', 'indexCIA')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::get('dashboard/citas/crc', 'indexCRC')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::post('dashboard/citas/get_citas', 'get_citas')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);
    Route::get('dashboard/citas/add', 'add')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/citas/save', 'save')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/citas/get_horarios', 'get_horarios')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::get('dashboard/citas/edit/{id}', 'edit')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::get('dashboard/citas/view/{id}', 'view')->middleware(['auth', 'verified', 'role:superadmin|admin|gestorsede|liquidador|lidercallcenter']);
    Route::post('dashboard/citas/update', 'update')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::post('dashboard/citas/delete', 'delete')->middleware(['auth', 'verified', 'role:superadmin']);
    Route::get('dashboard/citas/configuracion', 'configuracion')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::post('dashboard/citas/get_estados', 'get_estados')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/citas/add_estados', 'add_estados')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::get('dashboard/citas/edit_estados/{id}', 'edit_estados')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::post('dashboard/citas/update_estados', 'update_estados')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::post('dashboard/citas/delete_estados', 'delete_estados')->middleware(['auth', 'verified', 'role:superadmin']);
    Route::post('dashboard/citas/change_estado', 'change_estado')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
    Route::post('dashboard/citas/change_estado_verificado', 'change_estado_verificado')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|lidercallcenter']);
    Route::post('dashboard/citas/dowload', 'dowload')->middleware(['auth', 'verified', 'role:superadmin|admin|gestorsede|lidercallcenter']);

    Route::post('dashboard/citas/get_seguimiento_cita', 'get_seguimiento_cita')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|liquidador|lidercallcenter']);

    Route::get('dashboard/citas/get_new_records', 'get_new_records')->middleware(['auth', 'verified', 'role:superadmin|admin|callcenter|gestorsede|lidercallcenter']);
})->name('citas');
//Usuarios
Route::controller(UsersController::class)->group(function () {
    Route::get('dashboard/usuarios', 'index')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::post('dashboard/usuarios/get', 'get')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::post('dashboard/usuarios/save', 'save')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::post('dashboard/usuarios/update', 'update')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::post('dashboard/usuarios/delete', 'delete')->middleware(['auth', 'verified', 'role:superadmin']);
    Route::post('dashboard/usuarios/get_user', 'get_user')->middleware(['auth', 'verified', 'role:superadmin|admin|lidercallcenter']);
    Route::get('dashboard/usuarios/logout', 'logout');
})->name('usuarios');
//CRON
Route::controller(AlertController::class)->group(function () {
    Route::get('sincronizaralert', 'index');
})->name('sincronizaralert');

//LIQUIDADOR
Route::controller(CitasController::class)->group(function () {
    Route::get('dashboard/liquidador', 'indexLiquidador')->middleware(['auth', 'verified', 'role:superadmin|admin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/cda', 'indexLiquidadorCDA')->middleware(['auth', 'verified', 'role:superadmin|admin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/cea', 'indexLiquidadorCEA')->middleware(['auth', 'verified', 'role:superadmin|admin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/cia', 'indexLiquidadorCIA')->middleware(['auth', 'verified', 'role:superadmin|admin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/crc', 'indexLiquidadorCRC')->middleware(['auth', 'verified', 'role:superadmin|admin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/get_citas', 'get_citas_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/save', 'save')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/get_horarios', 'get_horarios')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/edit/{id}', 'edit')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/view/{id}', 'view')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/update', 'update')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/delete', 'delete')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/get_servicio_liquidador', 'get_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/add_servicio_liquidador', 'add_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::get('dashboard/liquidador/edit_servicio_liquidador/{id}', 'edit_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/update_servicio_liquidador', 'update_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/change_servicio_liquidador', 'change_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/change_estado_servicio_liquidador', 'change_estado_servicio_liquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/updateComentario', 'updateComentario')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/updatePagoMasivo', 'updatePagoMasivo')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
    Route::post('dashboard/liquidador/dowloadLiquidador', 'dowloadLiquidador')->middleware(['auth', 'verified', 'role:superadmin|liquidador|lidercallcenter']);
})->name('liquidador');


/*
Route::get('/sedes', function () {
    return 'sedes';
    //return view('dashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('sedes');
$user = User::find(1);  // Encuentra al usuario con ID 1
$user->assignRole('admin');
use Spatie\Permission\Models\Role;
Role::create(['name' => 'admin', 'guard_name' => 'web']);
*/



require __DIR__ . '/auth.php';
