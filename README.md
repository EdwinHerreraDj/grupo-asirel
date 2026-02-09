# ERP de Gestión de Obras – ASIREL

ERP web desarrollado a medida para una empresa constructora, orientado a la **gestión económica y administrativa de obras**, control de certificaciones, facturación y seguimiento mensual del trabajo ejecutado.

El sistema está diseñado para reflejar **flujos reales del sector de la construcción**, no como un CRUD genérico.

---

## 🧠 Objetivo del proyecto

Centralizar en una única aplicación:

- Presupuestos de venta (contratado)
- Certificaciones mensuales (ejecutado)
- Comparativas económicas por oficio
- Facturación de ventas
- Proformas mensuales
- Gestión documental
- Control de usuarios y roles

Con **coherencia fiscal**, trazabilidad y control de estados.

---

## 🛠️ Stack tecnológico

### Backend
- **Laravel**
- **Livewire 3**
- PHP 8+
- MySQL

### Frontend
- Blade
- Tailwind CSS
- JavaScript
- Vite

### Otros
- DomPDF (PDFs)
- Git / GitHub

---

## 📦 Módulos principales

### Obras
- Alta y gestión de obras
- Asociación de clientes
- Contexto central del sistema

---

### Presupuesto de Venta
- Presupuesto contratado por oficio
- Unidad, cantidad, precio unitario e importe
- Editable en cualquier momento
- Base para comparativas y control de desviaciones

---

### Certificaciones
- Certificaciones por obra
- Múltiples capítulos por número de certificación
- Estados controlados:
  - pendiente
  - aceptada
  - facturada
- Líneas técnicas independientes
- Generación de informe PDF bajo demanda

---

### Comparativa Mensual de Certificaciones
Comparación real entre:
- **Contratado** (presupuesto de venta)
- **Ejecutado** (certificaciones)

Por oficio:
- Origen mes anterior
- Ejecución del mes
- Acumulado
- Pendiente
- Importes económicos

Este módulo reproduce el control mensual real utilizado en empresas constructoras.

---

### Facturación de Ventas
- Facturación manual
- Facturación directa desde certificaciones
- Series de facturación independientes
- Numeración fiscal controlada
- Estados:
  - borrador
  - emitida
  - enviada
  - pagada / anulada
- Preparado para adaptación a VeriFactu

---

### Proformas
- Proformas mensuales por obra
- Agrupación por periodo
- Selección de certificaciones
- Control previo a la facturación real

---

### Gestión documental
- Sistema tipo Drive
- Carpetas por entidad
- Control de archivos
- Base para gestión futura de caducidades

---

## 🔐 Arquitectura y criterios

- Separación clara entre:
  - datos contractuales
  - datos ejecutados
  - datos facturados
- Estados explícitos y coherentes
- Evita duplicidades de importes
- Transacciones y bloqueos en procesos críticos
- Código orientado a mantenibilidad y escalabilidad

---

## 🚀 Instalación básica

```bash
composer install
npm install
npm run build
php artisan migrate
php artisan serve
