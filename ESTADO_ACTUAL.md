# Estado actual — Corralón Materiales

## Última sesión
F02 — Detalle de producto completado y verificado. Refactor carrito.js aplicado.

## Completado

### Infraestructura
- Docker local con WordPress + WooCommerce + MailHog
- Plugin `corralon-materiales` con arquitectura Clean + PSR-4
- Autoload Composer, stubs WooCommerce para Intelephense
- Deploy automático: develop → main → Hostinger vía GitHub Actions

### Dominio y datos
- `Producto` (entidad): id, nombre, precio, precio_oferta, categorias (array), stock, descripcion, sku, imagen_url
- `ProductoRepository`: findAll(), findPaginado(), contarTotal(), getCategorias(), findById()
- `ProductoRepositoryInterface`: contrato completo con los 5 métodos
- `LineaPresupuesto`, `SolicitudPresupuesto` (entidades de dominio)
- `CarritoRepository` / `CarritoRepositoryInterface`
- `MailerInterface` / `WpMailer`

### Funcionalidades MVP
- F03 ✅ — Botón "Solicitar presupuesto" en carrito clásico WooCommerce
  - Modal + formulario + endpoint REST + email vía MailHog + confirmación al cliente
  - Botón "Finalizar compra" nativo oculto vía CSS (reversible para fase 2)
- F01 ✅ — Catálogo de productos con filtros por categoría
  - Shortcode [catalogo_productos] en página /catalogo/
  - Tabs horizontales por categoría (Vanilla JS, sin jQuery)
  - Carga infinita con IntersectionObserver (12 productos iniciales)
  - Botón "Solicitar presupuesto" en cada tarjeta → agrega al carrito y redirige
  - Link al detalle en imagen y nombre de cada tarjeta
  - Endpoint REST GET /wp-json/corralon/v1/catalogo (categoria, pagina, por_pagina, permalink)
  - Assets con filemtime para cache-busting automático
  - Arquitectura preparada para precio/oferta en Fase 2 (<!-- FASE 2: precio -->)
- F02 ✅ — Detalle de producto
  - Plantilla single-product.php sobreescrita desde el plugin (woocommerce_locate_template)
  - Layout 2 columnas: imagen grande (40%) + info (60%), responsive a 1 columna en mobile
  - Badges de categoría, descripción completa, botón "Solicitar presupuesto"
  - Sección de productos relacionados por categoría (máx 4, excluye el producto actual)
  - Assets con filemtime, cargados condicionalmente solo en is_product()
  - <!-- FASE 2: precio --> marcado en plantilla y JS

### Arquitectura JS
- `carrito.js` — módulo compartido que expone `window.rdtCarrito.agregarAlCarrito()`
  Captura el texto original del botón antes de modificarlo (genérico, no hardcodeado)
  Registrado con `wp_register_script` (idempotente) y declarado como dependencia en
  CatalogoHooks y ProductoDetalleHooks — WordPress garantiza el orden de carga
- `catalogo.js` — tabs + carga infinita + IntersectionObserver, delega carrito a rdtCarrito
- `detalle.js` — botón principal + relacionados, delega carrito a rdtCarrito

### Conceptos aprendidos
- Constructor promotion con `private readonly`
- Dependency Inversion: depender de interfaces, no de implementaciones
- MailHog: captura de emails en entorno Docker local
- Cart Block vs carrito clásico de WooCommerce
- filemtime(): versionado automático de assets para evitar caché del browser
- IntersectionObserver: carga infinita sin scroll listeners
- WC_Product_Query: filtros por categoría y paginación
- woocommerce_locate_template: sobreescribir plantillas de WooCommerce desde un plugin
- wp_kses_post(): sanitización de HTML en outputs de WordPress
- wp_register_script() idempotente: registrar el mismo handle múltiples veces es seguro
- Módulo JS compartido via window global + dependencias de WordPress
- DRY aplicado a assets JS: extraer lógica común a módulo independiente
- Verificación estática de código con Claude vía MCP

## Pendiente MVP
- F04 — Historial de presupuestos (panel admin)
- Eliminar shortcode de prueba `ShortcodeProductosTest` antes de producción

## Próxima sesión
F04 — Historial de presupuestos en panel admin de WordPress.

## Deuda técnica conocida
- `is_cart()` en capa UI (`CarritoPresupuestoHooks`) — aceptable para MVP
