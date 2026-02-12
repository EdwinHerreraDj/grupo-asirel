<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DriveApp\FoldersController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FichajeController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\GastosVariosController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\MaterialesController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SubcontrataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriveApp\FileController;
use App\Http\Controllers\GastosEmpresaController;
use App\Http\Controllers\GastoGeneralEmpresaController;
use App\Http\Controllers\Informes\InformeController;
use App\Http\Controllers\FacturasRecibidasController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CategoriaGastoEmpresaController;
use App\Http\Controllers\CertificacionController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Api\ClienteController as ApiClienteController;
use App\Http\Controllers\CertificacionDetalleController;
use App\Http\Controllers\FacturasVentasController;
use App\Http\Controllers\FacturaSeriesController;
use App\Http\Controllers\PresupuestoVentaController;
use App\Http\Controllers\Api\Drive\FolderController;
use App\Http\Controllers\Api\Drive\FileController as ApiFileController;
use App\Http\Controllers\Api\ProveedorController as ApiProveedorController;
use App\Http\Controllers\Api\Drive\SearchController;
use App\Models\File;
use Spatie\FlareClient\Api;

/* Drive React (API-style, session-based) */

Route::middleware('auth')->prefix('api')->group(function () {

    /* Clientes API */
    Route::get('clientes', [ApiClienteController::class, 'index']);
    Route::post('clientes', [ApiClienteController::class, 'store']);
    Route::get('clientes/{id}', [ApiClienteController::class, 'show']);
    Route::put('clientes/{id}', [ApiClienteController::class, 'update']);
    Route::delete('clientes/{id}', [ApiClienteController::class, 'destroy']);

    /* Rutas para proveedores */
    Route::get('proveedores', [ApiProveedorController::class, 'index']);
    Route::post('proveedores', [ApiProveedorController::class, 'store']);
    Route::get('proveedores/{id}', [ApiProveedorController::class, 'show']);
    Route::put('proveedores/{id}', [ApiProveedorController::class, 'update']);
    Route::delete('proveedores/{id}', [ApiProveedorController::class, 'destroy']);

    /* Buscador de folder files */
    Route::get('drive/search', [SearchController::class, 'search'])->name('api.drive.search');

    /* Rutas de folder Componentes DRIVE */
    Route::prefix('folders')->group(function () {
        Route::get('{id}/content', [FolderController::class, 'getContent']);
        Route::post('/', [FolderController::class, 'store']);
        Route::put('{id}', [FolderController::class, 'update']);
        Route::delete('{id}', [FolderController::class, 'destroy']);
        Route::post('{id}/move', [FolderController::class, 'move']);
        Route::get('{id}/download', [FolderController::class, 'download']);
    });

    /* Rutas de archivos Componentes DRIVE */
    Route::prefix('files')->group(function () {
        Route::get('expiring', [ApiFileController::class, 'expiringFiles']);
        Route::post('/', [ApiFileController::class, 'store']);
        Route::put('{id}', [ApiFileController::class, 'update']);
        Route::delete('{id}', [ApiFileController::class, 'destroy']);
        Route::get('{id}/download', [ApiFileController::class, 'download']);
        Route::post('{id}/move', [ApiFileController::class, 'move']);
        Route::post('{id}/extract', [ApiFileController::class, 'extract']);
    });
});





require __DIR__ . '/auth.php';

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'home'])->name('root');

    /* Rutas explicitas */
    Route::get('/home', [RoutingController::class, 'home'])->name('home');
    Route::get('/logout', [RoutingController::class, 'logout'])->name('logout_action');
    Route::get('/unidad', [PageController::class, 'index'])->name('unidad');

    /* Control de CRUDS para los users */
    Route::resource('users', UserController::class);
    Route::resource('obras', ObraController::class);
    Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login.logs');

    // Mi Unidad
    Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa.index');
    Route::get('/empresa/drive-app', [FoldersController::class, 'index'])->name('empresa.driveApp');

    // Rutas de Obras
    Route::get('/obra/{id}/informe-general', [ObraController::class, 'informeGeneral'])->name('obra.informe.general');
    Route::get('/obras/{id}/informe-general-excel', [ObraController::class, 'informeGeneralExcel'])->name('obras.informeGeneralExcel');

    // Rutas de Documentos
    Route::get('/obras/{id}/documentos', [DocumentoController::class, 'index'])->name('obras.documentos');

    // Rutas de Gastos
    Route::get('/obras/{id}/gastos', [GastosController::class, 'index'])->name('obras.gastos');

    // Rutas de Materiales
    Route::get('/obras/{id}/gastos/materiales', [MaterialesController::class, 'index'])->name('obras.gastos.materiales');
    Route::post('/materiales', [MaterialesController::class, 'store'])->name('materiales.store');
    Route::delete('/materiales/{id}', [MaterialesController::class, 'destroy'])->name('materiales.destroy');
    Route::get('/obra/{id}/materiales/informes/excel', [MaterialesController::class, 'materialesExcel'])->name('obra.materiales.excel');
    Route::get('/obra/{id}/materiales/informes/pdf', [MaterialesController::class, 'descargarPDF'])->name('obra.materiales.pdf');
    Route::get('/obra/informe/{id}', [MaterialesController::class, 'verInforme'])->name('obra.informe');

    // Rutas de Alquileres
    Route::get('/obras/{id}/gastos/alquileres', [AlquilerController::class, 'index'])->name('obras.gastos.alquileres');
    Route::post('/alquileres', [AlquilerController::class, 'store'])->name('alquileres.store');
    Route::delete('/alquileres/{id}', [AlquilerController::class, 'destroy'])->name('alquileres.destroy');
    Route::get('/alquileres/informe/{id}', [AlquilerController::class, 'verInforme'])->name('alquileres.informe');
    Route::get('/obra/{id}/alquileres/informes/excel', [AlquilerController::class, 'alquileresExcel'])->name('obra.alquileres.excel');
    Route::get('/obra/{id}/alquileres/informes/pdf', [AlquilerController::class, 'descargarPDF'])->name('obra.alquileres.pdf');

    // Rutas de Subcontratas
    Route::get('/obras/{id}/gastos/subcontratas', [SubcontrataController::class, 'index'])->name('obras.gastos.subcontratas');
    Route::post('/subcontratas', [SubcontrataController::class, 'store'])->name('subcontratas.store');
    Route::delete('/subcontratas/{id}', [SubcontrataController::class, 'destroy'])->name('subcontratas.destroy');
    Route::get('/subcontratas/informe/{id}', [SubcontrataController::class, 'verInforme'])->name('subcontratas.informe');
    Route::get('/obra/{id}/subcontratas/informes/excel', [SubcontrataController::class, 'subcontratasExcel'])->name('obra.subcontratas.excel');
    Route::get('/obra/{id}/subcontrata/informes/pdf', [SubcontrataController::class, 'descargarPDF'])->name('obra.subcontrata.pdf');

    // Rutas de Gastos Varios
    Route::get('/obras/{id}/gastos-varios', [GastosVariosController::class, 'index'])->name('obras.gastos-varios');
    Route::post('/gastos-varios', [GastosVariosController::class, 'store'])->name('gastos-varios.store');
    Route::delete('/gastos-varios/{id}', [GastosVariosController::class, 'destroy'])->name('gastos-varios.destroy');
    Route::get('/gastos-varios/informe/{id}', [GastosVariosController::class, 'verInforme'])->name('gastos-varios.informe');
    Route::get('/obra/{id}/gastosvarios/informes/excel', [GastosVariosController::class, 'gastosVariosExcel'])->name('obra.gastosvarios.excel');
    Route::get('/obra/{id}/gastosvarios/informes/pdf', [GastosVariosController::class, 'descargarPDF'])->name('obra.gastosvarios.pdf');

    // Rutas de Ventas Certificaciones
    Route::get('/obras/{id}/ventas', [VentaController::class, 'index'])->name('obras.ventas');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->name('ventas.destroy');
    Route::get('/ventas/informe/{id}', [VentaController::class, 'verInforme'])->name('ventas.informe');
    Route::get('/obra/{id}/ventas/informes/excel', [VentaController::class, 'ventasExcel'])->name('obra.ventas.excel');
    Route::get('/obra/{id}/ventas/informes/pdf', [VentaController::class, 'descargarPDF'])->name('obra.ventas.pdf');
    Route::get('empresa/certificaciones/informe', [CertificacionController::class, 'informe'])->name('empresa.certificaciones.informe');


    // Certificaciones Asirel
    Route::get('/obras/{id}/certificaciones', [CertificacionController::class, 'index'])->name('obras.certificaciones');
    Route::get('/empresa/obras/{obra}/certificaciones/facturar', [CertificacionController::class, 'facturar'])->name('empresa.certificaciones.facturar');
    Route::get('/empresa/certificaciones/{certificacion}', [CertificacionDetalleController::class, 'show'])->name('empresa.certificaciones.show');


    // Rutas de Fichajes
    Route::get('/fichajes/{id}', [FichajeController::class, 'index'])->name('obras.fichajes');
    Route::put('/resumen/{id}', [FichajeController::class, 'update'])->name('resumen.update');
    Route::get('/obra/{id}/fichajes/informes/excel', [FichajeController::class, 'fichajesExcel'])->name('obra.fichajes.excel');

    // Rutas de Drive App (Carpetas y Archivos)
    Route::get('/descargar/{file}', [FileController::class, 'descargar'])->name('files.descargar');
    Route::get('/descargar-carpeta/{id}', [FileController::class, 'descargarCarpeta'])->name('folders.descargarCarpeta');
    Route::get('/drive/ver/{file}', [FileController::class, 'ver'])->name('drive.ver');

    // Rutas de Gastos de la Empresa
    Route::get('/empresa/gastos-empresa', [GastosEmpresaController::class, 'index'])->name('empresa.gastosEmpresa');
    Route::get('/empresa/categorias-gastos', [CategoriaGastoEmpresaController::class, 'index'])->name('categorias.empresa.index');
    Route::prefix('empresa/gastos')->group(function () {
        Route::get('/export/pdf', [GastoGeneralEmpresaController::class, 'exportarPDF'])
            ->name('empresa.gastos.exportar.pdf');

        Route::get('/export/excel', [GastoGeneralEmpresaController::class, 'exportarExcel'])
            ->name('empresa.gastos.exportar.excel');
    });


    // Rutas de Informes Generales
    Route::get('/informes', [InformeController::class, 'Index'])->name('informes.index');
    Route::get('/informes/exportar/coste-total-obras', [InformeController::class, 'exportarCosteTotalObras'])
        ->name('informes.exportar.coste-total-obras');
    Route::get('/informes/exportar/facturacion-total', [InformeController::class, 'exportarFacturacionTotal'])
        ->name('informes.exportar.facturacion-total');
    Route::get('/informes/exportar/rentabilidad', [InformeController::class, 'exportarRentabilidad'])
        ->name('informes.exportar.rentabilidad');
    Route::get('/informes/exportar/coste-venta-mensual', [InformeController::class, 'exportarCosteVentaMensual'])
        ->name('informes.exportar.coste-venta-mensual');

    // Modulos de Asirel
    Route::get('/obras/{obra}/facturas-recibidas', [FacturasRecibidasController::class, 'index'])->name('obras.facturas-recibidas');
    // Rutas de Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores');
    //Rutas de Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes');

    // Rutas de Facturas de Ventas
    Route::get('/empresa/facturas-series', [FacturaSeriesController::class, 'index'])->name('empresa.facturas-series');
    Route::get('/empresa/facturas-ventas', [FacturasVentasController::class, 'index'])->name('empresa.facturas-ventas');
    Route::get('/empresa/facturas-ventas/{factura}', [FacturasVentasController::class, 'detalle'])->name('empresa.facturas-ventas.detalle');
    // routes/web.php

    Route::get('/empresa/facturas-ventas/{factura}/pdf', [FacturasVentasController::class, 'pdf'])->name('empresa.facturas-ventas.pdf');
    // Rutas de Prseupuestos de Venta
    Route::get('/obras/{obra}/presupuesto-venta', [PresupuestoVentaController::class, 'index'])->name('obras.presupuesto-venta');

    //Rutas de consevar la session activa
    Route::get('/ping', function () {
        return response()->noContent();
    })->name('ping');


    // Rutas dinamicas - DEBE IR AL FINAL DE TODO
    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
