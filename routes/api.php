<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\Dashboard\SedesController;
use App\Http\Controllers\Dashboard\EmpresasController;
use App\Services\WhatsappService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(LoadController::class)->group(function () {
    Route::get('detalles-cita/{id}', 'getDetallesCita');
});

Route::controller(sedesController::class)->group(function () {
    Route::get('obtener-ubicaciones', 'obtenerUbicaciones');
});

Route::controller(EmpresasController::class)->group(function () {
    Route::post('verificar-empresa', 'validarExistenciaEmpresa');
    Route::post('registrar-empresa-paquete', 'registrarEmpresaPaquete');
});

Route::controller(WhatsappService::class)->group(function () {
    Route::post('webhook-whatsapp', 'webhookWhatsapp');
});
