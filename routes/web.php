<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DriveApp\FoldersController;
use App\Http\Controllers\EmpresaController;
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
use App\Http\Controllers\CosteTeoricoController;
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
    Route::post('clientes', [ApiClienteController::class, 'store'])->middleware('role:admin,super_admin');
    Route::get('clientes/{id}', [ApiClienteController::class, 'show']);
    Route::put('clientes/{id}', [ApiClienteController::class, 'update'])->middleware('role:admin,super_admin');
    Route::delete('clientes/{cliente}', [ApiClienteController::class, 'destroy'])->middleware('role:admin,super_admin');

    /* Rutas para proveedores */
    Route::get('proveedores', [ApiProveedorController::class, 'index']);
    Route::post('proveedores', [ApiProveedorController::class, 'store'])->middleware('role:admin,super_admin');
    Route::get('proveedores/{id}', [ApiProveedorController::class, 'show']);
    Route::put('proveedores/{id}', [ApiProveedorController::class, 'update'])->middleware('role:admin,super_admin');
    Route::delete('proveedores/{id}', [ApiProveedorController::class, 'destroy'])->middleware('role:admin,super_admin');

    /* ===== DRIVE: solo admin y super_admin ===== */
    Route::middleware('role:admin,super_admin')->group(function () {
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

    /* ===== RECURSOS HUMANOS: solo admin y super_admin ===== */
    Route::middleware('role:admin,super_admin')->prefix('rrhh')->group(function () {
        Route::get('empleados', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'index']);
        Route::post('empleados', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'store']);
        Route::get('empleados/{empleado}', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'show']);
        Route::put('empleados/{empleado}', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'update']);
        Route::post('empleados/{empleado}/baja', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'baja']);
        Route::post('empleados/{empleado}/reingreso', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'reingreso']);
        Route::get('documentacion-pendiente', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'pendientes']);
        Route::get('obras', [\App\Http\Controllers\Api\Rrhh\EmpleadoController::class, 'buscarObras']);

        Route::get('tipos-documento', [\App\Http\Controllers\Api\Rrhh\TipoDocumentoController::class, 'index']);
        Route::post('tipos-documento', [\App\Http\Controllers\Api\Rrhh\TipoDocumentoController::class, 'store']);
        Route::put('tipos-documento/{tipo}', [\App\Http\Controllers\Api\Rrhh\TipoDocumentoController::class, 'update']);

        // Ausencias, calendario y festivos (fase 2)
        Route::get('empleados/{empleado}/ausencias', [\App\Http\Controllers\Api\Rrhh\AusenciaController::class, 'index']);
        Route::post('empleados/{empleado}/ausencias', [\App\Http\Controllers\Api\Rrhh\AusenciaController::class, 'store']);
        Route::put('ausencias/{ausencia}', [\App\Http\Controllers\Api\Rrhh\AusenciaController::class, 'update']);
        Route::delete('ausencias/{ausencia}', [\App\Http\Controllers\Api\Rrhh\AusenciaController::class, 'destroy']);
        Route::get('calendario', [\App\Http\Controllers\Api\Rrhh\CalendarioController::class, 'index']);
        Route::get('tipos-ausencia', [\App\Http\Controllers\Api\Rrhh\TipoAusenciaController::class, 'index']);
        Route::post('tipos-ausencia', [\App\Http\Controllers\Api\Rrhh\TipoAusenciaController::class, 'store']);
        Route::put('tipos-ausencia/{tipo}', [\App\Http\Controllers\Api\Rrhh\TipoAusenciaController::class, 'update']);
        Route::get('festivos', [\App\Http\Controllers\Api\Rrhh\FestivoController::class, 'index']);
        Route::post('festivos', [\App\Http\Controllers\Api\Rrhh\FestivoController::class, 'store']);
        Route::post('festivos/nacionales', [\App\Http\Controllers\Api\Rrhh\FestivoController::class, 'nacionales']);
        Route::delete('festivos/{festivo}', [\App\Http\Controllers\Api\Rrhh\FestivoController::class, 'destroy']);

        // Nóminas, anticipos, formación y sanciones (fase 3)
        Route::get('nominas', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'mes']);
        Route::post('nominas/marcar-pagadas', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'marcarPagadas']);
        Route::get('empleados/{empleado}/nominas', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'porEmpleado']);
        Route::post('empleados/{empleado}/nominas', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'store']);
        Route::put('nominas/{nomina}', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'update']);
        Route::delete('nominas/{nomina}', [\App\Http\Controllers\Api\Rrhh\NominaController::class, 'destroy']);
        Route::get('empleados/{empleado}/anticipos', [\App\Http\Controllers\Api\Rrhh\AnticipoController::class, 'index']);
        Route::post('empleados/{empleado}/anticipos', [\App\Http\Controllers\Api\Rrhh\AnticipoController::class, 'store']);
        Route::put('anticipos/{anticipo}', [\App\Http\Controllers\Api\Rrhh\AnticipoController::class, 'update']);
        Route::delete('anticipos/{anticipo}', [\App\Http\Controllers\Api\Rrhh\AnticipoController::class, 'destroy']);
        Route::get('empleados/{empleado}/cursos', [\App\Http\Controllers\Api\Rrhh\CursoController::class, 'index']);
        Route::post('empleados/{empleado}/cursos', [\App\Http\Controllers\Api\Rrhh\CursoController::class, 'store']);
        Route::put('cursos/{curso}', [\App\Http\Controllers\Api\Rrhh\CursoController::class, 'update']);
        Route::delete('cursos/{curso}', [\App\Http\Controllers\Api\Rrhh\CursoController::class, 'destroy']);
        Route::get('empleados/{empleado}/sanciones', [\App\Http\Controllers\Api\Rrhh\SancionController::class, 'index']);
        Route::post('empleados/{empleado}/sanciones', [\App\Http\Controllers\Api\Rrhh\SancionController::class, 'store']);
        Route::put('sanciones/{sancion}', [\App\Http\Controllers\Api\Rrhh\SancionController::class, 'update']);
        Route::delete('sanciones/{sancion}', [\App\Http\Controllers\Api\Rrhh\SancionController::class, 'destroy']);

        // Turnos, cuadrante, alertas e informes (fase 4)
        Route::get('turnos', [\App\Http\Controllers\Api\Rrhh\TurnoController::class, 'index']);
        Route::post('turnos', [\App\Http\Controllers\Api\Rrhh\TurnoController::class, 'store']);
        Route::put('turnos/{turno}', [\App\Http\Controllers\Api\Rrhh\TurnoController::class, 'update']);
        Route::get('cuadrante', [\App\Http\Controllers\Api\Rrhh\CuadranteController::class, 'index']);
        Route::post('cuadrante/asignar', [\App\Http\Controllers\Api\Rrhh\CuadranteController::class, 'asignar']);
        Route::post('cuadrante/copiar', [\App\Http\Controllers\Api\Rrhh\CuadranteController::class, 'copiar']);
        Route::post('cuadrante/eliminar', [\App\Http\Controllers\Api\Rrhh\CuadranteController::class, 'eliminar']);
        Route::get('alertas', [\App\Http\Controllers\Api\Rrhh\AlertaController::class, 'index']);
        Route::get('informes/resumen', [\App\Http\Controllers\Api\Rrhh\InformeController::class, 'resumen']);
        Route::get('informes/exportar/{tipo}', [\App\Http\Controllers\Api\Rrhh\InformeController::class, 'exportar']);
    });
});





require __DIR__ . '/auth.php';

Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
    Route::get('', [RoutingController::class, 'home'])->name('root');

    /* Rutas explicitas */
    Route::get('/home', [RoutingController::class, 'home'])->name('home');
    Route::get('/unidad', [PageController::class, 'index'])->name('unidad');

    /* Control de CRUDS para los users */
    Route::resource('users', UserController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:admin,super_admin');
    Route::get('/login-logs', [LoginLogController::class, 'index'])
        ->middleware('role:super_admin')
        ->name('login.logs');

    // Mi Unidad
    Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa.index');
    Route::get('/empresa/configuracion', [EmpresaController::class, 'configuracion'])->middleware('role:admin,super_admin')->name('empresa.configuracion');
    Route::get('/empresa/drive-app', [FoldersController::class, 'index'])->middleware('role:admin,super_admin')->name('empresa.driveApp');
    Route::get('/rrhh', [\App\Http\Controllers\RrhhController::class, 'index'])->middleware('role:admin,super_admin')->name('rrhh.index');

    // Rutas de Obras
    Route::get('/obra/{id}/informe-general', [ObraController::class, 'informeGeneral'])->name('obra.informe.general');
    Route::get('/obras/{id}/informe-general-excel', [ObraController::class, 'informeGeneralExcel'])->name('obras.informeGeneralExcel');

    // Rutas de Documentos
    Route::get('/obras/{id}/documentos', [DocumentoController::class, 'index'])->name('obras.documentos');

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
    // Certificaciones Asirel
    Route::get('/obras/{id}/certificaciones', [CertificacionController::class, 'index'])->name('obras.certificaciones');
    Route::get('/empresa/certificaciones/{certificacion}', [CertificacionDetalleController::class, 'show'])->name('empresa.certificaciones.show');



    // Drive: vista previa de archivos (solo admin y super_admin)
    Route::get('/drive/ver/{file}', [FileController::class, 'ver'])->middleware('role:admin,super_admin')->name('drive.ver');

    // Rutas de Gastos de la Empresa
    Route::get('/empresa/gastos-empresa', [GastosEmpresaController::class, 'index'])->middleware('role:admin,super_admin')->name('empresa.gastosEmpresa');
    Route::get('/empresa/categorias-gastos', [CategoriaGastoEmpresaController::class, 'index'])->middleware('role:admin,super_admin')->name('categorias.empresa.index');
    Route::prefix('empresa/gastos')->middleware('role:admin,super_admin')->group(function () {
        Route::get('/export/pdf', [GastoGeneralEmpresaController::class, 'exportarPDF'])
            ->name('empresa.gastos.exportar.pdf');

        Route::get('/export/excel', [GastoGeneralEmpresaController::class, 'exportarExcel'])
            ->name('empresa.gastos.exportar.excel');
    });


    // Rutas de Informes Generales
    Route::get('/informes', [InformeController::class, 'index'])->middleware('role:admin,super_admin')->name('informes.index');
    Route::get('/informes/exportar/liquidacion-iva', [InformeController::class, 'exportarLiquidacionIva'])
        ->middleware('role:admin,super_admin')->name('informes.exportar.liquidacion-iva');
    Route::get('/informes/exportar/analisis-bruto-obras', [InformeController::class, 'exportarAnalisisBrutoObras'])
        ->middleware('role:admin,super_admin')->name('informes.exportar.analisis-bruto-obras');
    Route::get('/informes/exportar/retenciones-obra', [InformeController::class, 'exportarRetencionesObra'])
        ->middleware('role:admin,super_admin')->name('informes.exportar.retenciones-obra');

    // Modulos de Asirel
    Route::get('/facturas-recibidas', [FacturasRecibidasController::class, 'global'])->name('facturas-recibidas.global');
    Route::get('/obras/{obra}/facturas-recibidas', [FacturasRecibidasController::class, 'index'])->name('obras.facturas-recibidas');
    // Rutas de Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->middleware('role:admin,super_admin')->name('proveedores');
    //Rutas de Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->middleware('role:admin,super_admin')->name('clientes');

    // Rutas de Facturas de Ventas
    Route::get('/empresa/facturas-series', [FacturaSeriesController::class, 'index'])->middleware('role:admin,super_admin')->name('empresa.facturas-series');
    Route::get('/empresa/facturas-ventas', [FacturasVentasController::class, 'index'])->middleware('role:admin,super_admin')->name('empresa.facturas-ventas');
    Route::get('/empresa/facturas-ventas/{factura}', [FacturasVentasController::class, 'detalle'])->middleware('role:admin,super_admin')->name('empresa.facturas-ventas.detalle');
    // routes/web.php

    Route::get('/empresa/facturas-ventas/{factura}/pdf', [FacturasVentasController::class, 'pdf'])->middleware('role:admin,super_admin')->name('empresa.facturas-ventas.pdf');
    Route::get('/empresa/facturas-ventas/{factura}/pdf/copia', [FacturasVentasController::class, 'pdfCopia'])->middleware('role:admin,super_admin')->name('empresa.facturas-ventas.pdf.copia');
    Route::get('/empresa/facturas-ventas/{factura}/documentos/{documento}/descargar', [FacturasVentasController::class, 'documentoDescargar'])->middleware('role:admin,super_admin')->name('empresa.facturas-ventas.documentos.descargar');
    // Rutas de Presupuesto (Coste te\u00f3rico + Presupuesto de venta)
    // Tareas (kanban personal)
    Route::get('/tareas', [\App\Http\Controllers\TareasController::class, 'index'])->name('tareas.index');

    Route::get('/coste-teorico', [CosteTeoricoController::class, 'global'])->name('coste-teorico.global');
    Route::get('/presupuesto-venta', [PresupuestoVentaController::class, 'global'])->name('presupuesto-venta.global');
    Route::get('/obras/{obra}/coste-teorico', [CosteTeoricoController::class, 'index'])->name('obras.coste-teorico');
    Route::get('/obras/{obra}/presupuesto-venta', [PresupuestoVentaController::class, 'index'])->name('obras.presupuesto-venta');

    //Rutas de consevar la session activa
    Route::get('/ping', function () {
        return response()->noContent();
    })->name('ping');


});
