# AI_CONTEXT – ERP de Obras (Grupo Asirel)

Contexto para IA y desarrolladores: arquitectura, invariantes y reglas que no se deben romper.
Última revisión: 2026-09-14.

---

## 1. Qué es

ERP web a medida para una constructora. **Control económico por obra**:

```
Contratado (presupuesto de venta) → Ejecutado (certificaciones) → Facturado → Pendiente
```

No es un CRM ni contabilidad general.

---

## 2. Stack y organización

- **Backend:** Laravel 10, PHP 8.1+, MySQL.
- **Livewire 3** (+ PowerGrid): obras, documentos de obra, facturas de venta (listado, detalle, formulario), facturas recibidas, gastos de empresa, empresa, informes.
- **React 19 en "islas"** montadas desde Blade (`resources/js/react/app.jsx` busca `<div id="react-...">` y lee `data-*`): certificaciones, presupuesto de venta, coste teórico, tareas, drive, clientes, proveedores.
  - Llaman a una API JSON autenticada por **sesión**: `routes/api.php` (`auth:sanctum` stateful) y el grupo `/api` de `routes/web.php` (`auth`).
- **PDF:** DomPDF. **Excel:** maatwebsite/excel.
- **Assets:** el build de Vite se commitea en `public/build`; producción no compila.
- Estados nuevos siempre como `string` (hay `enum` heredados en tablas antiguas; no crear más).

---

## 3. Entidades principales

| Área | Modelos |
|---|---|
| Obra | `Obra` (raíz), `ObraGastoCategoria` (oficio / capítulo, pertenece a una obra) |
| Contratado | `ObraPresupuestoVenta` (capítulo de venta: obra + oficio), `PresupuestoVentaPartida` (medición, precio, importe) |
| Coste teórico | `GastoInicialPartida` (la partida de venta puede enlazarse con `coste_partida_id`) |
| Ejecutado | `Certificacion`, `CertificacionDetalle`, `CertificacionEvento` |
| Facturado | `FacturaVenta`, `FacturaVentaDetalle`, `FacturaVentaPago`, `FacturaVentaDocumento`, `FacturaVentaReimpresion`, `FacturaSerie` |
| Compras / gastos | `FacturaRecibida`, `GastoGeneralEmpresa`, `CategoriaGastoEmpresa` |
| Otros | `Tarea`, `Documento`, `DocumentoTipo`, `Folder` / `File` (drive), `Cliente`, `Proveedor`, `User`, `LoginLog` |

---

## 4. Certificaciones

- `numero_certificacion` agrupa **capítulos**: un registro en `certificaciones` por oficio, todos con el mismo número.
- Estados:
  - `estado_certificacion`: `pendiente` | `aceptada`
  - `estado_factura`: `pendiente` | `facturada`
  - `estado_cobro`: informativo (no fiscal), solo en certificaciones aceptadas.
- **Solo editable si `estado_certificacion = pendiente`.** Aceptar exige al menos una línea. Anular devuelve una aceptada a pendiente si no está facturada.
- **Líneas:** se crean desde partidas del presupuesto de venta **de la misma obra y oficio**; el precio sale de la partida. Si la cantidad supera lo pendiente contratado se avisa (bloqueo suave, forzable).
- **Cálculo fiscal solo en `CertificacionCalculator::recalcular()`**, siempre tras crear, editar o borrar líneas:
  ```
  base_imponible    = SUM(importe_linea)
  iva_importe       = base × iva_porcentaje / 100
  retencion_importe = base × retencion_porcentaje / 100
  total             = base + iva − retención      (todo redondeado a 2 decimales)
  ```
  No recalcula certificaciones facturadas.
- IVA y retención son iguales en todos los capítulos de un número; se aplican a todos a la vez.
- Cada transición de estado registra un `CertificacionEvento`.

### Comparativa mensual

Contratado (presupuesto de venta) frente a ejecutado (`certificacion_detalles.cantidad`), filtrado por `certificaciones.fecha_ingreso`, sin filtrar por estado (intencional). Por oficio: mes, origen anterior, a origen, pendiente e importes con el precio del presupuesto. Solo lectura.

---

## 5. Facturación de venta

### 5.1 Desde certificaciones (`FacturaVentaGenerator`)

- Se factura el **número de certificación completo**. Requisitos: todos los capítulos aceptados y pendientes de factura, mismo cliente, mismo IVA y retención, serie seleccionada.
- Todo en `DB::transaction` con `lockForUpdate()` sobre las certificaciones y la serie. **No quitar los locks.**
- Numeración fiscal con `factura_series.ultimo_numero`; nunca con autoincrement.
- Los totales de la factura son la suma de los totales de los capítulos y se fijan **antes** de crear las líneas.
- **Modos de desglose** (solo presentación; en todos, la suma de líneas = base imponible):
  - `resumen` (por defecto): una línea por capítulo.
  - `lineas`: una línea por cada línea de certificación, con concepto, unidad, cantidad, precio e importe congelados y `certificacion_detalle_id`.
  - `lineas_comentarios`: igual que `lineas` más el comentario de cada línea.
- **Orden:** capítulos por id y, dentro, líneas por id; el generador guarda `orden` secuencial. `FacturaVenta::detalles()` ordena por `orden` y después por `id`.
- En facturas desglosadas, el **PDF y la vista web agrupan por capítulo** (cabecera con el nombre del oficio y subtotal). Ese nombre se lee del oficio al pintar; no está congelado en la línea.
- Tras emitir, las certificaciones pasan a `facturada` y se registra el evento.

### 5.2 Manuales (`FacturaVentaService`)

- `borrador` (editable) → `emitida`. Una factura no borrador no admite cambios de líneas (`asegurarEditable`).

### 5.3 PDF, anulación y cobro

- **PDF original**: se genera al emitir y es inmutable (`pdf_original_generado_at`). Las **copias** se generan al vuelo, se marcan como copia y registran una reimpresión.
- **Anular** una factura de certificaciones devuelve sus certificaciones a `pendiente` (de factura y de certificación) si ninguna otra factura viva del mismo grupo las mantiene, y registra el evento.
- `estado_cobro` de la factura es informativo y no toca importes ni numeración.

---

## 6. Seguridad y permisos

- **Roles** en `users.role`: `super_admin` | `admin` | `user`. No hay registro público: las altas se hacen en `/users`.
- **Middleware `role`** (`App\Http\Middleware\EnsureUserHasRole`, uso `->middleware('role:admin,super_admin')`).
  - Solo admin y super_admin: usuarios, facturas de venta (incluidos PDF, copia y documentos), series, clientes y proveedores (páginas y escrituras de la API; la lectura de la API es para todos), gastos de empresa y exportaciones, informes, configuración de empresa.
  - Solo super_admin: logs de login.
  - Es el **mismo criterio que el menú lateral**: toda sección que el menú oculte a un rol debe protegerse también en su ruta.
- **Usuarios:** un admin no crea, edita ni borra super_admins ni asigna ese rol; nadie puede borrarse a sí mismo; no se puede quitar el rol al único super_admin.
- **Integridad de recursos anidados:** todo recurso de la URL debe pertenecer a su padre.
  - Línea ↔ certificación; partida certificada ↔ obra y oficio de la certificación.
  - Partida, capítulo y documento ↔ obra.
  - Tareas: solo admin, persona asignada o creador.
- **No hay rutas catch-all**: cada página necesita una ruta explícita con nombre.
- **Cerrar sesión solo con `POST /logout`** (formulario con CSRF).

---

## 7. Reglas que no se deben romper

1. No editar certificaciones que no estén en `pendiente`.
2. No recalcular importes fuera de `CertificacionCalculator`.
3. No facturar si algún capítulo no está aceptado, o con clientes, IVA o retención distintos.
4. No quitar transacciones ni `lockForUpdate` de la emisión.
5. No usar autoincrement como numeración fiscal.
6. El desglose de líneas de factura es presentación: no debe cambiar los totales fiscales.
7. No sobrescribir el PDF original de una factura emitida.
8. No añadir secciones de admin sin proteger su ruta con `role`.
9. Comprobar siempre que un recurso anidado pertenece a su padre.
10. No romper la agrupación por `numero_certificacion`.

---

## 8. Desarrollo, tests y despliegue

- **Tests:** `php artisan test`.
  - `tests/Feature`: `UserManagementTest`, `AccesoModulosPorRolTest`, `IntegridadPertenenciaTest`, `RutasYLogoutTest`, `FacturaDesgloseCapitulosTest`, `CertificacionFiscalTest`, `FacturacionFiscalTest`, `RelacionesModelosTest`.
  - `tests/Unit/ReferenciasClasesMayusculasTest`.
  - Usan `DatabaseTransactions` sobre la base de datos de `.env`. **Nunca ejecutarlos contra producción.**
- **Mayúsculas en namespaces:** producción es Linux y distingue mayúsculas. `App\Services\facturas` va en minúscula (carpeta `app/Services/facturas`). El test de mayúsculas lo vigila, porque en Windows no falla.
- **Relaciones Eloquent:** si la clave foránea no sigue la convención de Laravel, indicarla explícitamente (p. ej. `belongsTo(FacturaVenta::class, 'factura_venta_id')`). Si no, la relación devuelve `null` sin avisar. `RelacionesModelosTest` lo vigila.
- **Ramas:** `develop` para trabajar, `main` para producción.
- **Despliegue** (en el servidor, carpeta del proyecto):
  ```bash
  git pull origin main
  php artisan migrate --force        # solo si hay migraciones nuevas
  php artisan optimize:clear         # si cambian rutas o config
  php artisan optimize               # producción usa caché de rutas y config
  ```
- **UI:** diseños responsive (móvil, tablet, escritorio); nunca `alert`, `confirm`, `prompt` ni `wire:confirm` (usar modales de la app); al abrir un modal, bloquear el scroll del `body`.
