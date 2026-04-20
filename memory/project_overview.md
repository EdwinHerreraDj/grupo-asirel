---
name: Project overview — ERP Obras ASIREL
description: Stack, domain, architecture y puntos críticos del ERP de obras de Grupo Asirel
type: project
---

**Stack:** Laravel 10 + Livewire 3 + MySQL. Frontend dual: Livewire (la mayoría) + React 19 (solo módulo Drive, estilo API). Tailwind 3, Vite, PowerGrid 6.7, dompdf, maatwebsite/excel.

**Dominio:** ERP de control económico por obra (no CRM, no contabilidad). Ejes: Contratado (presupuesto venta) → Ejecutado (certificaciones) → Facturado (FacturaVenta) → Pendiente.

**Entidades raíz:**
- `Obra` (root), `Cliente`, `Proveedor`, `ObraGastoCategoria` (oficios)
- `ObraPresupuestoVenta` + `PresupuestoVentaPartida` (contratado)
- `Certificacion` + `CertificacionDetalle` (capítulos agrupados por `numero_certificacion`)
- `FacturaVenta` + `FacturaVentaDetalle` + `FacturaVentaPago` + `FacturaSerie` (numeración fiscal independiente)
- Gastos: `Material`, `Alquiler`, `Subcontrata`, `GastosVarios`, `GastoBase`, `FacturaRecibida`
- Soporte: `Documento`, `File`/`Folder` (Drive), `Fichaje`, `LoginLog`, `GastoInicialPartida`

**Invariantes críticas (de AI_CONTEXT.md):**
- Recálculo fiscal SOLO vía `App\Services\CertificacionCalculator::recalcular()`
- No editar certificación si `estado_certificacion != pendiente`
- Facturación agrupa por `numero_certificacion`, requiere todos capítulos aceptados, mismo cliente, misma serie, mismo IVA/retención
- Transacciones con `lockForUpdate` en emisión de facturas
- Estados en BD como string (no enums)
- Numeración fiscal desacoplada de autoincrement (tabla `factura_series`)

**Estructura `app/`:**
- `Models/` 30 archivos
- `Services/` 4 archivos: CertificacionCalculator, FacturaVentaGenerator, FacturaRecibidaCalculator, `facturas/FacturaPdfService`
- `Livewire/` 28 componentes (Empresa/Certificaciones, Empresa/FacturasVentas, Empresa/Gastos, Obras/*, Clientes, Proveedores, Drive, Documentos, Informes)
- `Http/Controllers/` 50 archivos (25 root + subdirs Api/, Admin/, Auth/, DriveApp/, Informes/)
- `Jobs/` 3 (CopiarDrive, EliminarDrive, Export)

**Riesgos detectados:**
1. Dos flujos de partidas: `GastoInicialPartida` + `PresupuestoVentaPartida` con cross-links recientes (migraciones 2026-03-24/25) → riesgo de desincronización.
2. Sin tests reales: solo 2 stubs `ExampleTest`.
3. Ruta catch-all dinámica (`{any}`, `{first}/{second}`, `{first}/{second}/{third}`) al final de `routes/web.php` — puede tragar rutas nuevas si se colocan después.
4. Sin config central para reglas fiscales/numeración (dispersa entre servicios).
5. Controllers root sprawl: 25 archivos sin subcarpetas por dominio.
6. React + Livewire conviviendo (Drive es React-only) → cuidado al proponer soluciones mixtas.

**Rama activa:** `develop` (main = producción). Último commit b94b738 "Modificacion en la vista de creacion de obra".

**Bloques estables:** presupuesto venta, certificaciones por capítulos, facturación manual y desde certificaciones, comparativa mensual, PDFs.
**En evolución:** mejora visual balances, asignación tareas por usuario, extensión adjudicaciones.
