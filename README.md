# Corralón Materiales — WordPress Plugin

Plugin WordPress para gestión de catálogo de productos y solicitudes de presupuesto, orientado a empresas de materiales de construcción.

Desarrollado con **Clean Architecture** y principios **SOLID**, desacoplando la lógica de negocio del framework de WordPress.

---

## Funcionalidades

- Catálogo de productos con filtros por categoría, búsqueda de texto y carga infinita
- Página de detalle de producto con productos relacionados
- Flujo de solicitud de presupuesto: carrito WooCommerce → modal → email al administrador y confirmación al cliente
- Panel de administración (WooCommerce → Presupuestos): historial paginado, gestión de estado y nota interna, responsive mobile
- Suscripción de clientes con registro como usuario WordPress y email de bienvenida
- Tema hijo `modern-blue-rdtecno` (repositorio independiente): header sticky, footer con botón flotante de WhatsApp, paleta y tipografía del proyecto

---

## Arquitectura

El plugin sigue Clean Architecture con separación estricta de capas. WordPress actúa únicamente como framework de entrega (REST API, hooks, CPT), sin infiltrarse en la lógica de negocio.

```
corralon-materiales/
├── corralon-materiales.php       ← Punto de entrada: carga el autoloader y arranca Bootstrap\Plugin
├── composer.json                 ← Autoload PSR-4, dependencias de desarrollo
├── assets/
│   ├── css/                      ← Hojas de estilo por funcionalidad
│   └── js/                       ← Scripts por funcionalidad
├── templates/
│   └── single-product.php        ← Plantilla de WooCommerce sobreescrita desde el plugin
└── includes/
    ├── Bootstrap/
    │   └── Plugin.php            ← Registro de hooks, composición del grafo de dependencias
    ├── Domain/                   ← Entidades puras sin dependencias de WordPress
    │   ├── Producto.php
    │   ├── Presupuesto.php
    │   ├── LineaPresupuesto.php
    │   ├── SolicitudPresupuesto.php
    │   └── PresupuestoEstado.php
    ├── Repositories/             ← Única capa que accede a WooCommerce y la base de datos
    │   ├── ProductoRepositoryInterface.php
    │   ├── ProductoRepository.php
    │   ├── PresupuestoRepositoryInterface.php
    │   └── PresupuestoRepository.php
    ├── Services/                 ← Lógica de negocio; orquesta repositorios y mailers
    │   ├── ProductoService.php
    │   └── PresupuestoService.php
    ├── Api/                      ← Controladores REST: validan, sanitizan y delegan al servicio
    │   ├── CatalogoController.php
    │   ├── PresupuestoController.php
    │   └── SuscripcionController.php
    ├── Ui/                       ← Registro de shortcodes, enqueue condicional de assets, hooks de WooCommerce
    │   ├── CatalogoHooks.php
    │   ├── ProductoDetalleHooks.php
    │   ├── CarritoPresupuestoHooks.php
    │   ├── HomeHooks.php
    │   └── WoocommerceHooks.php
    ├── Admin/                    ← Panel de administración interno
    │   ├── AdminPresupuestosPage.php
    │   └── AdminPresupuestosController.php
    ├── CPT/
    │   └── PresupuestoCpt.php    ← Custom Post Type para persistencia de presupuestos
    ├── Mail/
    │   ├── MailerInterface.php   ← Abstracción de envío de email
    │   ├── WpMailer.php          ← Implementación con wp_mail()
    │   └── SuscripcionMailer.php
    └── Shortcodes/
        └── HomeShortcode.php
```

### Flujo de dependencias

```
Bootstrap\Plugin
    │
    ├── instancia Repositories (concretos)
    ├── inyecta interfaces a Services y Controllers
    │
    ├── Api\CatalogoController(ProductoRepositoryInterface)
    ├── Api\PresupuestoController(PresupuestoService)
    │       └── PresupuestoService(MailerInterface, PresupuestoRepositoryInterface)
    └── Ui\CatalogoHooks(ProductoRepositoryInterface)
```

Los controllers y services dependen de interfaces, nunca de clases concretas. El único lugar donde se instancian las implementaciones concretas es `Bootstrap\Plugin`.

---

## Principios SOLID aplicados

**S — Single Responsibility:** cada clase tiene una responsabilidad única. `PresupuestoService` orquesta el flujo de negocio; `PresupuestoRepository` accede a los datos; `PresupuestoController` valida la request HTTP y delega.

**O — Open/Closed:** `ProductoRepositoryInterface` permite agregar una implementación `ScraperProductoRepository` (catálogo multi-proveedor vía scraping, planificado para Fase 2) sin modificar `ProductoService` ni ningún controller.

**L — Liskov Substitution:** cualquier implementación de `ProductoRepositoryInterface` o `PresupuestoRepositoryInterface` es intercambiable sin romper el sistema.

**I — Interface Segregation:** las interfaces son específicas y pequeñas. `MailerInterface` solo expone `send()`. Los services no están forzados a depender de contratos que no usan.

**D — Dependency Inversion:** los services y controllers dependen de abstracciones (`ProductoRepositoryInterface`, `MailerInterface`), no de implementaciones concretas. La composición ocurre en `Bootstrap\Plugin`.

---

## Decisiones técnicas relevantes

**WooCommerce Store API vs. `WC()->cart`**
La Store API (`/wc/store/v1/`) mantiene una sesión separada de `WC()->cart`. En contexto REST, el carrito no está disponible via `WC()->cart`. Solución: el frontend consulta la Store API antes de enviar el formulario y pasa las líneas del carrito en el payload. El nonce correcto es `wp_create_nonce('wc_store_api')` — no existe `wc_store_api_nonce()` en todas las versiones de WooCommerce.

**Separación plugin / tema hijo**
El plugin maneja exclusivamente la lógica de negocio. El header, footer, paleta y tipografía global viven en el tema hijo `modern-blue-rdtecno`. El shortcode `[home_corralon]` provee solo contenido — nunca layout estructural.

**Assets con `filemtime()` para cache-busting**
Todos los assets se versionan con `filemtime()` en lugar de strings hardcodeados. Cualquier cambio en el archivo invalida la caché automáticamente sin tocar el código de registro.

**Template override desde el plugin**
La plantilla `templates/single-product.php` sobreescribe la de WooCommerce con filtro `woocommerce_locate_template`, manteniendo el override dentro del plugin (sin depender del tema activo).

---

## Endpoints REST

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/wp-json/corralon/v1/catalogo` | Catálogo paginado con filtros por categoría y búsqueda |
| `POST` | `/wp-json/rdt/v1/presupuesto` | Envía solicitud de presupuesto con líneas del carrito |
| `POST` | `/wp-json/rdt/v1/suscripcion` | Registra suscriptor como usuario WordPress |

---

## Entorno local

Requiere Docker.

```bash
git clone https://github.com/RodoDut/corralon-wp.git
cd corralon-wp
composer install
docker compose up -d
```

WordPress disponible en `http://localhost:8080`. Emails capturados por MailHog en `http://localhost:8025`.

El tema hijo (`modern-blue-rdtecno`) vive en un repositorio separado. El `docker-compose.yml` lo monta como bind mount desde `../modern-blue-rdtecno`.

---

## Deploy

Push a `main` dispara el workflow de GitHub Actions que hace rsync vía SSH a Hostinger. El trabajo de desarrollo ocurre en la rama `develop`; solo se mergea a `main` cuando el cambio está verificado en local.

```
develop  →  pull request / merge  →  main  →  GitHub Actions  →  Hostinger (rsync SSH)
```

---

## Stack

- PHP 8.1+ con `declare(strict_types=1)` en todos los archivos
- WordPress + WooCommerce
- Composer con autoload PSR-4 (`RDT\Corralon\` → `includes/`)
- Vanilla JS (sin jQuery en código nuevo)
- GitHub Actions para CI/CD
