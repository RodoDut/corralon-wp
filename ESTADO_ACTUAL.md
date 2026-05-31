# Estado actual — Corralón Materiales

## Última sesión
F03 — Solicitud de presupuesto completado y verificado.

## Completado

### Infraestructura
- Docker local con WordPress + WooCommerce + MailHog
- Plugin `corralon-materiales` con arquitectura Clean + PSR-4
- Autoload Composer, stubs WooCommerce para Intelephense
- Deploy automático: develop → main → Hostinger vía GitHub Actions

### Dominio y datos
- `Producto` (entidad), `ProductoRepository`, `ProductoRepositoryInterface`
- `ProductoService::listarTodos()` con inyección de dependencias
- `LineaPresupuesto`, `SolicitudPresupuesto` (entidades de dominio)
- `CarritoRepository` / `CarritoRepositoryInterface` — única capa que toca WooCommerce
- `MailerInterface` / `WpMailer` — patrón Adapter sobre wp_mail()

### Funcionalidades MVP
- F03 ✅ — Botón "Solicitar presupuesto" en carrito clásico WooCommerce
  - Modal con formulario (nombre, email, teléfono, mensaje)
  - Endpoint REST POST /wp-json/rdt/v1/presupuesto
  - Email al corralón con detalle del carrito vía MailHog (local)
  - Popup de confirmación al cliente
  - Botón "Finalizar compra" nativo oculto vía CSS (reversible para fase 2)

### Conceptos aprendidos
- Constructor promotion con `private readonly`
- Dependency Inversion: depender de interfaces, no de implementaciones
- MailHog: captura de emails en entorno Docker local
- Cart Block vs carrito clásico de WooCommerce
- Verificación estática de código con Claude vía MCP

## Pendiente MVP

- F01 — Catálogo de productos con filtros
- F02 — Detalle de producto
- F04 — Historial de presupuestos (panel admin)
- Eliminar shortcode de prueba `ShortcodeProductosTest` antes del próximo commit

## Próxima sesión
Definir y arrancar F01 — Catálogo de productos con filtros por categoría.

## Deuda técnica conocida
- `is_cart()` en capa UI (`CarritoPresupuestoHooks`) — aceptable para MVP
- `WooAccountCustomizer` en rdt-centros-core acumula responsabilidades (documentado en README)
