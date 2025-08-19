# POS + Facturación Electrónica Guatemala (Laravel + Livewire)

Sistema de ventas con punto de venta (POS) y **integración a Facturación Electrónica en Línea (FEL) de Guatemala**, construido con **Laravel** y **Livewire**.

> ⚠️ Este README es una base sólida para iniciar el proyecto. Ajusta nombres, variables y flujos según tu certificador FEL, tu modelo de datos y tus reglas de negocio.

---

## ✨ Características

* **POS rápido** (teclado/lector de código de barras, búsqueda por nombre/sku).
* **Catálogo**: productos, categorías, impuestos, precios y descuentos.
* **Clientes**: creación rápida en caja, NIT/CUI, exentos/retención.
* **Inventario**: control por almacén, entradas/salidas y ajustes.
* **Ventas**: tickets, facturas, notas de crédito/débito.
* **Pagos**: efectivo, tarjeta, mixto y caja chica.
* **FEL Guatemala**: emisión, certificación, anulación y descarga de PDF/QR.
* **Reportes**: ventas diarias, cierres de caja, top productos/clientes.
* **Multi‑empresa/tienda/terminal** (opcional multi‑tenant).
* **Roles y permisos** con políticas.
* **Colas** para certificación asíncrona y reintentos.
* **Auditoría** de eventos (quién hizo qué y cuándo).

---

## 🧱 Arquitectura (alto nivel)

```
UI (Blade + Livewire) → Casos de uso (Services/Actions) → Dominio (Models) → Infra (Repos/Integraciones FEL)
                                               └→ Jobs (queues) → Logs/Auditoría
```

* **Livewire**: componentes reactivos para POS, carrito, búsqueda, cierre de caja.
* **Services/Actions**: `CreateSale`, `CertifyFEL`, `VoidFEL`, etc.
* **Integración FEL**: cliente HTTP encapsulado por certificador (InFile, Digifact, etc.).
* **Jobs**: certificación, reintentos, notificaciones.

---

## 🧰 Stack tecnológico

* **PHP** 8.2+
* **Laravel** 10/11/12
* **Livewire** 3+
* **Base de datos**: MySQL/MariaDB o PostgreSQL
* **Redis** (colas, cache, rate limiting)
* **Queues**: Laravel Horizon (opcional)
* **Auth**: Laravel Breeze/Jetstream (elige uno)
* **Testing**: Pest/PhpUnit
* **Docker** (opcional)

---

## 📦 Requisitos

* PHP 8.2+
* Composer 2+
* Node 18+
* MySQL 8+ / MariaDB 10.6+ / PostgreSQL 14+
* Redis 6+

---

## 🚀 Puesta en marcha

```bash
# 1) Clonar repositorio
git clone https://tu-repo.git pos-fel-gt && cd pos-fel-gt

# 2) Dependencias
composer install
npm install && npm run build # o npm run dev en desarrollo

# 3) Variables de entorno
cp .env.example .env
php artisan key:generate

# 4) Base de datos
php artisan migrate --seed

# 5) Storage y enlaces
php artisan storage:link

# 6) Colas (desarrollo)
php artisan queue:work
# o con Horizon
php artisan horizon

# 7) Servidor de desarrollo
php artisan serve
```

---

## 🔐 Variables de entorno (ejemplo)

```dotenv
APP_NAME="POS FEL GT"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_fel
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# FEL (ajusta a tu certificador)
FEL_CERTIFICADOR=digifact           # o infile, otro
FEL_ENV=sandbox                     # sandbox|production
FEL_BASE_URL=https://api.sandbox... # endpoint del certificador
FEL_USER=tu_usuario_api
FEL_PASSWORD=tu_password_api
FEL_SUSCRIPTOR=NIT_DE_TU_EMPRESA
FEL_EMISOR_NIT=1234567
FEL_EMISOR_NOMBRE="Mi Empresa, S.A."
FEL_EMISOR_AFILIACION=GEN          # régimen/afiliación
FEL_EMISOR_DIRECCION="Zona 1, Guatemala"
FEL_EMISOR_CODIGO_ESTABLECIMIENTO=1
FEL_TIMEOUT=30
```

> 💡 Mantén credenciales **fuera del repositorio**. Usa un vault/secret manager en producción.

---

## 📚 Modelos/claves (sugeridos)

* **Product**: `sku`, `name`, `price`, `tax_code`, `stock_by_warehouse`.
* **Customer**: `name`, `nit`, `email`, `address`.
* **Sale**: `series`, `number`, `customer_id`, `subtotal`, `tax`, `total`, `status` (`draft|certified|voided`).
* **SaleItem**: `sale_id`, `product_id`, `qty`, `price`, `discount`, `tax`.
* **Payment**: `sale_id`, `method`, `amount`, `reference`.
* **CashSession**: `user_id`, `opened_at`, `closed_at`, `opening_amount`, `closing_amount`.
* **FelDocument**: `sale_id`, `uuid`, `serie`, `numero`, `dte_type`, `certified_at`, `xml_path`, `pdf_path`.

---

## 🧮 Flujo de venta (POS)

1. Abrir **sesión de caja**.
2. Escanear/agregar productos al **carrito** (Livewire).
3. Seleccionar **cliente** o crear rápido (NIT/CUI; usar `CF` si aplica).
4. Aplicar **descuentos** y validar **existencias**.
5. Registrar **pago** (efectivo, tarjeta o mixto).
6. **Guardar Venta** (estado `draft`).
7. **Emitir FEL** → construir DTE → **certificar** con el proveedor.
8. Guardar **UUID**, serie/número, y **adjuntar XML/PDF**.
9. **Imprimir/Enviar** ticket y PDF de FEL.

---

## 🧾 Integración FEL (Guatemala)

> Implementación en capa `App/Services/Fel/` con un **cliente por certificador**.

### Tipos de DTE (comunes)

* **Factura** (FACT)
* **Nota de Crédito** (NC)
* **Nota de Débito** (ND)

### Casos de uso

* `CertifyFelDocumentAction`: recibe `Sale`, arma el **XML/JSON** requerido, firma/envía, persiste `uuid` y archivos.
* `VoidFelDocumentAction`: anula por UUID/serie-número con motivo y control de plazos.

### Errores y reintentos

* Colas con `retryUntil` y backoff exponencial.
* Reintento manual desde UI para ventas en estado **pendiente**.
* Log detallado de request/response (sin credenciales) y **tracking id** del certificador.

### Archivos

* Guardar **XML firmado** y **PDF** en `storage/app/fel/{year}/{month}/...`.
* Comando para **re-descargar** PDF/representación gráfica si el certificador lo provee.

---

## 🧩 Componentes Livewire (sugeridos)

* `pos.cart`
* `pos.product-search`
* `pos.customer-quick-create`
* `pos.payment`
* `pos.cash-session`
* `sales.index` / `sales.show`
* `reports.sales-daily`

---

## 🔗 Endpoints / Rutas (ejemplo)

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', PosController::class)->name('pos');
    Route::resource('products', ProductController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('sales', SaleController::class)->only(['index', 'show']);

    Route::post('/sales/{sale}/fel/certify', [FelController::class, 'certify'])
        ->name('sales.fel.certify');
    Route::post('/sales/{sale}/fel/void', [FelController::class, 'void'])
        ->name('sales.fel.void');
    Route::get('/sales/{sale}/fel/pdf', [FelController::class, 'pdf'])
        ->name('sales.fel.pdf');
});
```

---

## 🧪 Pruebas

```bash
# Unit y feature
php artisan test

# Cobertura (si usas Xdebug/PCOV)
vendor/bin/pest --coverage
```

* Pruebas de **cálculo de totales**, **impuestos**, **stock**, **flujos POS**.
* Pruebas de **integración FEL** con **fakes** (grabar respuestas sandbox) y **contratos** del cliente HTTP.

---

## 🛡️ Seguridad y permisos

* Políticas (`Gate/Policy`) por módulo.
* Rate limiting en rutas FEL.
* Auditoría con `ActivityLog` (quién, cuándo, desde dónde).
* Enmascarar datos sensibles en logs.

---

## 🧭 Convenciones de código

* **PSR-12**, **Arquitectura por capas** (Http/Domain/Services/Infra).
* **Commits** convencionales: `feat:`, `fix:`, `refactor:`, `chore:`.
* **Ramas**: `main`, `develop`, `feature/*`, `hotfix/*`.

---

## 🧑‍💻 Scripts útiles

```bash
php artisan fel:test-connection                 # ping al certificador
php artisan fel:retry-pending --since=24h       # reintenta pendientes recientes
php artisan fel:download-pdf {uuid}             # descarga representación gráfica
php artisan pos:close-cash --session=ID         # cierre forzado con reporte
```

---

## 🐳 Docker (opcional)

* Servicios: `app`, `nginx`, `mysql/postgres`, `redis`, `horizon`.
* Monta `./storage` como volumen persistente.

---

## 📈 Reportes sugeridos

* Ventas por día/usuario/terminal.
* Productos top y márgenes.
* Cierres de caja y diferencias.
* Documentos FEL emitidos/anulados por período.

---

## 🗺️ Roadmap

* Facturación **contingencia** (offline → lote FEL).
* **Multi‑moneda** y precios por lista.
* **Integración con impresora fiscal/escáner** (Web Serial/USB).
* **Soporte multi‑tenant** por empresa/sucursal.
* **Integración con FacturaScripts/WooCommerce** (si aplica).

---

## 📄 Licencia

El proyecto puede publicarse bajo **MIT** (ajusta si necesitas otra).

---

## 📫 Soporte

* Issues en el repositorio
* Contacto: [soporte@tu-dominio.com](mailto:soporte@tu-dominio.com)

---

## ✅ Checklist de despliegue (producción)

* [ ] `APP_ENV=production`, `APP_DEBUG=false`
* [ ] **HTTPS** habilitado (TLS)
* [ ] **Cache**: `config:cache`, `route:cache`, `view:cache`
* [ ] **Jobs/Horizon** activos y monitoreados
* [ ] **Backups** BD + `storage/`
* [ ] **Logs rotados** y centralizados
* [ ] Variables **FEL** de **producción**
* [ ] Reglas de **firewall** y **WAF**
* [ ] Supervisión de **errores** (Sentry, Bugsnag, etc.)
