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
    Route::post('admin/obras', [ObraController::class, 'store'])->name('obras.store');
    Route::delete('/obras/{id}', [ObraController::class, 'destroy'])->name('obras.destroy');
    Route::get('/obras/{id}/edit', [ObraController::class, 'edit'])->name('obras.edit');
    Route::put('/obras/{id}', [ObraController::class, 'update'])->name('obras.update');
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
    Route::get('/obra/{id}/gastosvarios/informes/excel', [GastosVariosController::class, 'gastosVariosExcel'])->name('obra.subcontratas.excel');
    Route::get('/obra/{id}/gastosvarios/informes/pdf', [GastosVariosController::class, 'descargarPDF'])->name('obra.gastosvarios.pdf');

    // Rutas de Ventas Certificaciones
    Route::get('/obras/{id}/ventas', [VentaController::class, 'index'])->name('obras.ventas');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->name('ventas.destroy');
    Route::get('/ventas/informe/{id}', [VentaController::class, 'verInforme'])->name('ventas.informe');
    Route::get('/obra/{id}/ventas/informes/excel', [VentaController::class, 'ventasExcel'])->name('obra.ventas.excel');
    Route::get('/obra/{id}/ventas/informes/pdf', [VentaController::class, 'descargarPDF'])->name('obra.ventas.pdf');

    // Certificaciones Asirel
    Route::get('/obras/{id}/certificaciones', [CertificacionController::class, 'index'])->name('obras.certificaciones');

    // Rutas de Fichajes
    Route::get('/fichajes/{id}', [FichajeController::class, 'index'])->name('obras.fichajes');
    Route::put('/resumen/{id}', [FichajeController::class, 'update'])->name('resumen.update');
    Route::get('/obra/{id}/fichajes/informes/excel', [FichajeController::class, 'fichajesExcel'])->name('obra.fichajes.excel');

    // Rutas de Drive App (Carpetas y Archivos)
    Route::get('/descargar/{file}', [FileController::class, 'descargar'])->name('files.descargar');
    Route::get('/descargar-carpeta/{id}', [FileController::class, 'descargarCarpeta'])->name('folders.descargarCarpeta');

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
    Route::get('/obras/{obra}/facturas-recibidas',[FacturasRecibidasController::class, 'index'])->name('obras.facturas-recibidas');
    Route::get('/proveedores',[ProveedorController::class, 'index'])->name('proveedores');


    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
