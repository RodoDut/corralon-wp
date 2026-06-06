# Estado actual — Corralón Materiales

## Última sesión
Migración de arquitectura visual: homepage delegada al tema hijo + Gutenberg.
Implementación de búsqueda, navegación por categorías, suscripción completa con emails y usuarios WordPress.

## Completado

### Infraestructura
- Docker local con WordPress + WooCommerce + MailHog
- Volumen `wp_data` agregado para persistir archivos de WordPress entre reinicios de Docker
- Plugin `corralon-materiales` con arquitectura Clean + PSR-4
- Autoload Composer, stubs WooCommerce para Intelephense
- Deploy automático: develop → main → Hostinger vía GitHub Actions
- Deploy migrado de `scp-action` a **rsync via SSH**
- Ruta correcta en Hostinger verificada: `domains/rdtecno.net/public_html/corralon/...`
- **Tema hijo `modern-blue-rdtecno`** creado como hijo de Twenty Twenty-Five:
  - `theme.json` con paleta completa del proyecto y tipografía Barlow/Barlow Condensed
  - `parts/header.html` — header global sticky oscuro con logo diamonds, búsqueda, carrito con badge
  - `parts/footer.html` — footer global oscuro con 3 columnas: logo+desc, contacto, redes SVG inline
  - `assets/header.css` — estilos del header con prefijo `mbt-`, responsive a 768px
  - `assets/header.js` — badge carrito (Store API), búsqueda con redirect a /catalogo/, pre-populate ?buscar=
  - Bind mount en docker-compose.yml: `./wp-content/themes/modern-blue-rdtecno:/var/www/html/wp-content/themes/modern-blue-rdtecno`

### Dominio y datos
- `Producto` (entidad): id, nombre, precio, precio_oferta, categorias (array), stock, descripcion, sku, imagen_url
- `ProductoRepository`: findAll(), findPaginado(), contarTotal(), getCategorias()
  - Soporte de búsqueda por texto vía filtro `woocommerce_product_data_store_cpt_get_products_query`
- `ProductoRepositoryInterface`: contrato completo incluyendo parámetro `$buscar`
- `LineaPresupuesto`, `SolicitudPresupuesto` (entidades de dominio)
- `CarritoRepository` / `CarritoRepositoryInterface` — archivos existentes sin uso activo
- `MailerInterface` / `WpMailer`
- `SuscripcionMailer` — emails de bienvenida al suscriptor y aviso al admin

### Funcionalidades MVP
- F03 ✅ — Botón "Solicitar presupuesto" en carrito clásico WooCommerce
  - Modal + formulario + endpoint REST + email vía MailHog + confirmación al cliente
  - Botón "Finalizar compra" nativo oculto vía CSS (reversible para fase 2)
  - Bug resuelto: carrito vacío en el email. Solución: JS consulta Store API y manda líneas en el payload
- F01 ✅ — Catálogo de productos con filtros por categoría y búsqueda
  - Shortcode [catalogo_productos] en página /catalogo/
  - Tabs horizontales por categoría (Vanilla JS, sin jQuery)
  - Carga infinita con IntersectionObserver (12 productos iniciales)
  - Botón "Solicitar presupuesto" en cada tarjeta → agrega al carrito y redirige
  - Endpoint REST GET /wp-json/corralon/v1/catalogo (categoria, buscar, pagina, por_pagina)
  - Lee ?categoria= y ?buscar= de la URL al inicializar
  - Assets con filemtime para cache-busting automático
- F02 ✅ — Detalle de producto
  - Plantilla `templates/single-product.php` con layout dos columnas
  - Breadcrumb, badge de categoría verde, nombre en Barlow Condensed
  - Descripción con borde izquierdo azul
  - Indicador de stock, bloque precio reservado (Fase 2)
  - Dos botones: "Solicitar presupuesto" (primario azul) + "Agregar al carrito" (secundario outline)
  - Sección productos relacionados (grilla 4 columnas)
  - Assets: `detalle.css` + `detalle.js`, Google Fonts Barlow registrado
- Homepage ✅ — Shortcode [home_corralon] (solo contenido, sin header/footer propios)
  - Barra de categorías con navegación a /catalogo/?categoria=SLUG
  - Hero full-width: título Barlow Condensed, subtexto, botón "Ver catálogo" → /catalogo/
  - Grilla de categorías con navegación a /catalogo/?categoria=SLUG
  - Banner de suscripción con formulario funcional
  - Header y footer provistos por el tema hijo (no por el shortcode)
- Suscripción ✅ — Flujo completo opt-in simple
  - Endpoint REST POST /corralon/v1/suscripcion
  - Crea usuario WordPress con rol `subscriber` + meta `rdt_suscriptor=1`
  - Evita duplicados (verifica email_exists antes de crear)
  - Guarda registro redundante en wp_options (rdt_corralon_suscriptores)
  - Email de bienvenida al suscriptor (vía MailHog en local)
  - Email de aviso al admin (admin_email de WordPress)
  - Modal "¡Suscripto! Revisá tu correo" con animación fadeIn
  - Modal se cierra con botón, click fuera, o tecla Escape
- Búsqueda ✅ — Desde header del tema y desde homepage
  - Input del header del tema redirige a /catalogo/?buscar=TÉRMINO
  - Catálogo pre-popula el input y filtra resultados al inicializar
- Endpoint suscripción ✅ — /corralon/v1/suscripcion con SuscripcionMailer inyectado

### Arquitectura visual actual
- **Tema hijo** `modern-blue-rdtecno`: header global, footer global, paleta, tipografía
- **Plugin**: lógica de negocio, shortcodes funcionales, endpoints REST
- **Homepage**: shortcode [home_corralon] dentro de página WordPress con plantilla "Blanco"
  El shortcode provee contenido (barra cats, hero, categorías, banner).
  El tema provee header y footer.
- **Catálogo**: shortcode [catalogo_productos] — sin header/footer propios, los hereda del tema

### Clases UI
- `includes/Ui/WoocommerceHooks.php` — redirige botones nativos de WooCommerce a /catalogo/
- `includes/Ui/HomeHooks.php` — registra [home_corralon] + enqueue condicional (catalog_url, nonce, rest_url_suscripcion)
- `includes/Ui/CatalogoHooks.php` — registra [catalogo_productos] + enqueue condicional
- `includes/Ui/ProductoDetalleHooks.php` — plantilla single-product + enqueue condicional
- `includes/Ui/CarritoPresupuestoHooks.php` — hooks del carrito + modal presupuesto

### Conceptos aprendidos esta sesión
- Tema hijo de Twenty Twenty-Five: `style.css` con `Template: twentytwentyfive` es suficiente para heredar
- `parts/header.html` y `parts/footer.html` en el tema hijo sobreescriben los del padre automáticamente
- FSE (Full Site Editing): WordPress resuelve template parts del tema hijo primero
- Bind mount en docker-compose.yml para desarrollo del tema hijo en local
- División correcta de responsabilidades: tema = estructura visual global, plugin = lógica de negocio
- `wp_create_user()` + `set_role()` + `add_user_meta()` para registrar suscriptores como usuarios WordPress
- `email_exists()` para evitar duplicados antes de crear usuario
- Modal JS puro: createElement + estilos inline + animación CSS inyectada dinámicamente
- `woocommerce_product_data_store_cpt_get_products_query` para inyectar búsqueda por texto en WC_Product_Query

## Pendiente MVP
- F04 — Historial de presupuestos (panel admin)
- Eliminar shortcode de prueba `ShortcodeProductosTest` antes de producción
- Homepage: reconstruir visualmente en Gutenberg (actualmente muestra el shortcode [home_corralon])

## Próxima sesión
- F04 historial de presupuestos (panel admin)
- O arrancar reconstrucción de homepage en Gutenberg

## Deuda técnica conocida
- `is_cart()` en capa UI (`CarritoPresupuestoHooks`) — aceptable para MVP
- Versionado de assets con `'1.0.0'` hardcodeado en `CarritoPresupuestoHooks` — migrar a filemtime
- `CarritoRepository` / `CarritoRepositoryInterface` — archivos sin uso activo. Eliminar o reutilizar en Fase 2
- `home.css` y `home.js` — vaciados, pendiente eliminar cuando se migre homepage a Gutenberg completamente
- Suscriptores en wp_options (rdt_corralon_suscriptores) — registro redundante, eliminar en Fase 2
- Homepage en shortcode — migrar a Gutenberg completo post-MVP
