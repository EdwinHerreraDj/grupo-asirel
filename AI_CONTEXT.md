Perfecto.
Entonces no es un texto comercial.
Es un **documento de contexto estructurado para IA**, para que cualquier modelo entienda cómo está construido el ERP y no rompa la arquitectura cuando trabajemos sobre él.

Voy directo.

---

# CONTEXTO ESTRUCTURAL – ERP DE OBRAS (ASIREL)

## 1. Naturaleza del sistema

Aplicación web tipo ERP para constructora.

Dominio principal:
Control económico de obras basado en:

* Contratado (presupuesto de venta)
* Ejecutado (certificaciones)
* Facturado
* Pendiente

No es un CRM.
No es contabilidad general.
Es control económico por obra.

---

## 2. Stack tecnológico

* Laravel (backend)
* Livewire 3 (UI reactiva controlada)
* MySQL
* PDFs generados bajo demanda
* Transacciones para procesos críticos
* Numeración fiscal desacoplada

No se usa:

* Inertia
* React en este proyecto
* Enums rígidos en base de datos

Estados siempre como string.

---

## 3. Entidades principales del dominio

### 3.1 Obra

Entidad raíz del sistema.

Relaciona:

* Presupuesto de venta
* Certificaciones
* Adjudicaciones
* Comparativa mensual
* Facturación

---

### 3.2 Presupuesto de Venta (Contratado)

Tabla: `obra_presupuestos_venta`

Características:

* Editable
* Basado en oficio (obra_gasto_categoria_id)
* unidad
* cantidad
* precio_unitario
* total (derivado)

Es la base económica contractual.

No se bloquea.
No depende de certificaciones.

---

### 3.3 Certificaciones (Ejecutado)

Tablas:

* `certificaciones`
* `certificacion_detalles`

Concepto clave:

Varias certificaciones pueden compartir el mismo `numero_certificacion` → eso representa capítulos.

Estados:

* estado_certificacion:

  * pendiente
  * aceptada

* estado_factura:

  * pendiente
  * facturada

Reglas críticas:

* No se pueden crear capítulos si estado_factura = facturada.
* La facturación se hace agrupando por numero_certificacion.
* Se usa fecha_ingreso como fecha real de certificación.

Perfecto.
Aquí tienes el **AI_CONTEXT.md completo y consolidado** del módulo de Certificaciones + Comparativa + Facturación, listo para pegar directamente en tu proyecto.

---

# AI CONTEXT – MÓDULO DE CERTIFICACIONES

ERP ASIREL – CONTROL ECONÓMICO DE OBRAS

---

# 1. Propósito del módulo

El módulo de Certificaciones gestiona la ejecución económica de una obra, estructurada por:

* Contratado (Presupuesto de venta)
* Ejecutado (Certificaciones y sus líneas)
* Facturado (FacturaVenta)
* Pendiente

La unidad lógica principal es:

```
numero_certificacion
```

Una certificación global puede tener **varios capítulos**, donde cada capítulo es un registro independiente en la tabla `certificaciones` con distinto oficio (`obra_gasto_categoria_id`) pero mismo número.

---

# 2. Estructura de datos

## 2.1 Tabla: certificaciones (cabecera)

Cada registro representa un capítulo.

Campos relevantes:

* obra_id
* cliente_id
* numero_certificacion
* obra_gasto_categoria_id
* fecha_ingreso (fecha operativa)
* fecha_contable
* fecha_vencimiento

Fiscalidad persistida:

* base_imponible
* iva_porcentaje
* iva_importe
* retencion_porcentaje
* retencion_importe
* total

Estados:

* estado_certificacion

  * pendiente
  * aceptada

* estado_factura

  * pendiente
  * facturada

---

## 2.2 Tabla: certificacion_detalles (líneas)

Cada línea contiene:

* certificacion_id
* concepto
* unidad
* cantidad
* precio_unitario
* importe_linea

`importe_linea = cantidad × precio_unitario`

---

# 3. Flujo de creación

## 3.1 Crear certificación

Se crea cabecera con:

* base_imponible = 0
* iva_importe = 0
* retencion_importe = 0
* total = 0
* estado_certificacion = pendiente
* estado_factura = pendiente

La certificación nace vacía.
Después se añaden líneas.

---

## 3.2 Crear capítulo dentro de una certificación

Se crea otro registro en `certificaciones` con:

* mismo numero_certificacion
* mismo obra_id
* mismo cliente_id
* mismos porcentajes fiscales
* distinto obra_gasto_categoria_id

Restricciones:

* No se permite si estado_factura = facturada
* No se permite duplicar oficio dentro del mismo número

---

# 4. Gestión de líneas

Las líneas se gestionan en el componente Detalle.

## Regla de edición

Solo editable si:

```
estado_certificacion === 'pendiente'
```

Si está aceptada → bloqueada completamente.

---

## Guardar línea

1. Validar datos.
2. Calcular importe_linea.
3. Guardar línea.
4. Llamar a:

```
CertificacionCalculator::recalcular()
```

Siempre recalcular después de:

* crear línea
* editar línea
* eliminar línea

---

# 5. Servicio oficial de cálculo fiscal

Clase:

```
App\Services\CertificacionCalculator
```

Método:

```
recalcular(Certificacion $certificacion)
```

Fórmulas oficiales:

```
base_imponible = SUM(importe_linea)
iva_importe = base × (iva_porcentaje / 100)
retencion_importe = base × (retencion_porcentaje / 100)
total = base + iva - retencion
```

Todos los valores se guardan redondeados a 2 decimales.

Invariantes:

* No recalcular fuera de este servicio.
* Total siempre debe cumplir: total = base + iva - retencion.
* Base siempre es suma exacta de líneas.

---

# 6. Modificación de impuestos

Desde Detalle:

* Solo si estado_certificacion = pendiente.
* Se actualizan todas las certificaciones con el mismo numero_certificacion.
* Se recalculan todas.

Invariante:

Dentro de un mismo numero_certificacion todos los capítulos deben tener:

* mismo iva_porcentaje
* mismo retencion_porcentaje

---

# 7. Aceptación de certificación

Requisitos:

* Debe tener al menos una línea.
* Debe estar en pendiente.

Efecto:

```
estado_certificacion = 'aceptada'
```

Después de aceptada:

* No se puede editar.
* No se pueden modificar impuestos.
* No se pueden añadir/eliminar líneas.

---

# 8. Facturación desde certificaciones

Unidad facturable:

```
numero_certificacion
```

Se factura el grupo completo de capítulos.

---

## 8.1 Requisitos para facturar

Para facturar un número:

1. Todos los capítulos del número deben existir.
2. Todos deben estar:

   * estado_certificacion = aceptada
   * estado_factura = pendiente
3. Todos deben tener mismo cliente.
4. Todos deben tener mismo IVA y retención.
5. Se debe seleccionar una serie activa.

---

## 8.2 Concurrencia

La emisión se hace dentro de:

```
DB::transaction
```

Con:

```
lockForUpdate()
```

Sobre:

* certificaciones del número
* serie de facturación

No eliminar ni simplificar estos locks.

---

## 8.3 Creación de factura

Se crea FacturaVenta con:

* estado = emitida
* origen = certificacion
* codigo_certificacion = numero_certificacion
* cliente_id
* obra_id
* base = suma bases capítulos
* iva = suma iva capítulos
* retencion = suma retención capítulos
* total = suma total capítulos

---

## 8.4 Líneas de factura

No se copian líneas técnicas.

Se crea 1 línea por capítulo:

* concepto = "Certificación X – Oficio"
* cantidad = 1
* precio_unitario = base_imponible del capítulo
* importe_linea = base_imponible

---

## 8.5 Marcado post-factura

Se actualizan certificaciones:

```
estado_factura = 'facturada'
```

Solo las aceptadas y pendientes.

---

# 9. Comparativa mensual

Comparación entre:

* Presupuesto de venta (contratado)
* Certificaciones (ejecutado)

Fuente ejecutado:

```
certificacion_detalles.cantidad
```

Filtrado por:

```
certificaciones.fecha_ingreso
```

No se filtra por estado.

Cálculos por oficio:

```
mes = suma cantidades mes
origen_anterior = suma cantidades hasta mes anterior
a_origen = origen_anterior + mes
pendiente = contrato - a_origen

importe_mes = mes × precio_unitario_presupuesto
importe_origen = a_origen × precio_unitario_presupuesto
```

---

# 10. Control contra presupuesto

En Detalle se compara:

* Cantidad contratada (ObraPresupuestoVenta)
* Cantidad certificada acumulada por oficio

Si excede:

* Se muestra alerta.
* No bloquea guardado.

---

# 11. Riesgos conocidos

1. Decimales no aceptan string vacío.
2. Dos flujos de creación de capítulo.
3. Validaciones fiscales dentro de transacción deben lanzar excepción para rollback seguro.
4. Comparativa incluye certificaciones pendientes y facturadas (es intencional actualmente).

---

# 12. Reglas que la IA no debe romper

* No permitir edición si estado_certificacion != pendiente.
* No facturar si existe capítulo no aceptado.
* No facturar con cliente distinto.
* No copiar líneas técnicas a factura.
* No eliminar locks ni transacciones.
* No recalcular fuera de CertificacionCalculator.
* No usar autoincrement como numeración fiscal.


---

### 3.4 Facturación de Venta

Tabla: `facturas_venta`

No depende de autoincrement para numeración fiscal.
Existe tabla separada de contadores por serie.

Estados:

* borrador
* emitida
* enviada
* pagada
* anulada

Tipos de origen:

* manual
* desde certificaciones

Reglas:

* Si es manual → empieza en borrador.
* Si es desde certificaciones → se crea directamente emitida.
* No se puede editar una factura emitida.
* Se bloquea por transacción y lockForUpdate.

---

### 3.5 Pivot factura-certificación

Tabla: `factura_venta_certificacion`

Permite:

* Una factura agrupa varias certificaciones
* Todas deben tener mismo cliente
* Todas deben estar aceptadas
* Todas deben estar estado_factura = pendiente

Al facturar:

* Se marcan como facturadas
* Se genera factura consolidada

---

### 3.6 Adjudicaciones

Tabla principal: `obra_adjudicaciones`

Desglose técnico:

* `adjudicacion_detalles`

No se toca `certificacion_detalles`.

Adjudicación representa contrato por oficio.
Certificación representa ejecución real.

---

## 4. Comparativa mensual

Base de cálculo:

Contratado (Presupuesto de venta)
vs
Ejecutado (Certificaciones)

Se calcula:

* Medición contratada
* Mes actual
* Acumulado
* Pendiente
* Importe mes
* Importe a origen

Usa fecha_ingreso como filtro mensual.

No modifica datos.
Solo lectura y cálculo.

---

## 5. Reglas arquitectónicas que NO se deben romper

1. No duplicar importes innecesariamente.
2. No mezclar lógica fiscal con lógica técnica.
3. No usar enums en base de datos.
4. No usar autoincrement como numeración fiscal.
5. No permitir edición de estados cerrados.
6. No copiar líneas técnicas al facturar → solo resumen por oficio.
7. No romper la agrupación por numero_certificacion.

---

## 6. Filosofía del sistema

El ERP está construido bajo estas premisas:

* Modelo explícito y entendible
* Separación clara entre:

  * contratado
  * ejecutado
  * facturado
* Estados controlados
* Procesos críticos protegidos con transacciones
* Sin lógica implícita
* Sin automatismos ocultos

---

## 7. Estado actual estable

Bloques cerrados:

* Presupuesto de venta
* Certificaciones por capítulos
* Facturación manual
* Facturación desde certificaciones
* Comparativa mensual
* PDF certificaciones

En evolución:

* Mejora visual balances
* Asignación de tareas por usuario
* Extensión módulo adjudicaciones


