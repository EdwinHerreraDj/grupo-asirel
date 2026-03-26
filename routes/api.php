<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Drive\FolderController;
use App\Http\Controllers\Api\Drive\FileController;
use App\Http\Controllers\Api\PresupuestoVentaController;
use App\Http\Controllers\Api\GastoInicialController;
use App\Http\Controllers\Api\PresupuestoVentaPdfController;
use App\Http\Controllers\Api\Certificaciones\CertificacionController;
use App\Http\Controllers\Api\Certificaciones\CertificacionDetalleController;
use App\Http\Controllers\Api\Certificaciones\CertificacionInformeController;
use App\Http\Controllers\Api\Certificaciones\ComparativaMensualController;
use App\Http\Controllers\Api\Certificaciones\CertificacionFacturarController;

// Drive — mantiene sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    Route::prefix('folders')->group(function () {
        Route::get('{id}/content', [FolderController::class, 'getContent']);
        Route::post('/', [FolderController::class, 'store']);
    });

    Route::prefix('files')->group(function () {
        Route::post('/', [FileController::class, 'store']);
    });

    // Obras
    Route::prefix('obras/{obra}')->group(function () {
        // Presupuesto de venta
        Route::get('presupuesto-venta', [PresupuestoVentaController::class, 'index']);
        Route::post('presupuesto-venta/sincronizar', [PresupuestoVentaController::class, 'sincronizar']);
        Route::post('presupuesto-venta/incrementar', [PresupuestoVentaController::class, 'incrementar']);
        Route::post('presupuesto-venta/restablecer', [PresupuestoVentaController::class, 'restablecer']);
        Route::post('presupuesto-venta/partidas', [PresupuestoVentaController::class, 'storePartida']);
        Route::put('presupuesto-venta/partidas/{partida}', [PresupuestoVentaController::class, 'updatePartida']);
        Route::delete('presupuesto-venta/partidas/{partida}', [PresupuestoVentaController::class, 'destroyPartida']);
        Route::get('presupuesto-venta/pdf', [PresupuestoVentaPdfController::class, 'descargar']);

        // Gastos iniciales
        Route::get('gastos-iniciales', [GastoInicialController::class, 'index']);
        Route::post('gastos-iniciales/partidas', [GastoInicialController::class, 'storePartida']);
        Route::put('gastos-iniciales/partidas/{partida}', [GastoInicialController::class, 'updatePartida']);
        Route::delete('gastos-iniciales/partidas/{partida}', [GastoInicialController::class, 'destroyPartida']);

        // Capítulos
        Route::post('capitulos', [GastoInicialController::class, 'storeCapitulo']);
        Route::put('capitulos/{capitulo}', [GastoInicialController::class, 'updateCapitulo']);
        Route::delete('capitulos/{capitulo}', [GastoInicialController::class, 'destroyCapitulo']);


        Route::get('certificaciones', [CertificacionController::class, 'index']);
        Route::post('certificaciones', [CertificacionController::class, 'store']);
        Route::post('certificaciones/capitulo', [CertificacionController::class, 'storeCapitulo']);
        Route::get('certificaciones/facturables', [CertificacionFacturarController::class, 'facturables']);
        Route::post('certificaciones/facturar', [CertificacionFacturarController::class, 'facturar']);
        Route::get('certificaciones/{numero}/capitulos', [CertificacionInformeController::class, 'capitulos']);
        Route::get('comparativa-mensual', [ComparativaMensualController::class, 'index']);
        Route::get('comparativa-mensual/pdf', [ComparativaMensualController::class, 'pdf']);
    });


    Route::prefix('certificaciones')->group(function () {
        Route::delete('{certificacion}', [CertificacionController::class, 'destroy']);
        Route::get('{certificacion}', [CertificacionDetalleController::class, 'show']);
        Route::post('{certificacion}/lineas', [CertificacionDetalleController::class, 'store']);
        Route::put('{certificacion}/lineas/{detalle}', [CertificacionDetalleController::class, 'update']);
        Route::delete('{certificacion}/lineas/{detalle}', [CertificacionDetalleController::class, 'destroy']);
        Route::put('{certificacion}/impuestos', [CertificacionDetalleController::class, 'impuestos']);
        Route::post('{certificacion}/aceptar', [CertificacionDetalleController::class, 'aceptar']);
        Route::post('informe-pdf', [CertificacionInformeController::class, 'pdf']);
    });
});
