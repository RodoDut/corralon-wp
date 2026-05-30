# SPEC — Corralón Materiales
**Versión:** 0.1  
**Última actualización:** 2026-05-30  
**Estado:** MVP en desarrollo

---

## 1. Problema que resuelve

El corralón no tiene presencia web ni sistema de consultas online. Los clientes no pueden ver el catálogo de productos ni hacer consultas fuera del horario comercial. El objetivo es proveer un sitio web profesional que muestre el catálogo, permita pedir presupuestos y facilite el contacto.

---

## 2. Usuarios

| Usuario | Rol |
|---|---|
| Dueño / Empleados | Administran productos, responden consultas y presupuestos desde el admin de WordPress |
| Clientes finales | Navegan el catálogo, arman un carrito y solicitan presupuesto |
| Clientes registrados | Cuenta propia, historial de presupuestos y acceso a ofertas especiales (Fase 2) |

---

## 3. Stack técnico

- **CMS:** WordPress
- **E-commerce:** WooCommerce (catálogo + carrito, sin pasarela de pagos)
- **Plugin principal:** `corralon-materiales` (PHP 8.1+, PSR-4, Clean Architecture)
- **Hosting:** Hostinger Business Web
- **Constructor visual:** Elementor (a confirmar)
- **Repositorio:** GitHub (rama `develop` → `main` con deploy automático vía GitHub Actions)

---

## 4. Arquitectura del plugin

```
corralon-materiales/
├── corralon-materiales.php       ← Bootstrap: includes, hooks, inicialización
├── includes/
│   ├── Domain/                   ← Entidades puras (Producto, Presupuesto)
│   ├── Repositories/             ← Acceso a datos (WooCommerce, CPT)
│   ├── Services/                 ← Lógica de negocio
│   ├── Api/                      ← Controladores REST (futuro)
│   └── Shortcodes/               ← Renderizado frontend (futuro)
├── vendor/                       ← Composer (autoload PSR-4)
└── SPEC.md                       ← Este archivo
```

**Principios aplicados:** SOLID, Clean Architecture. Los repositorios son la única capa que toca la base de datos. Los servicios orquestan repositorios. Los controladores solo validan y delegan.

---

## 5. Funcionalidades

### 5.1 MVP (en desarrollo)

| # | Funcionalidad | Estado |
|---|---|---|
| F01 | Catálogo de productos con categorías | 🔄 En desarrollo |
| F02 | Carrito de compras nativo de WooCommerce (sin pago, personalización en Fase 2) | ⬜ Pendiente |
| F03 | Solicitud de presupuesto por contenido del carrito | ⬜ Pendiente |
| F04 | Página "Quiénes somos" (misión, visión, ubicación) | ⬜ Pendiente |
| F05 | Formulario de consultas | ⬜ Pendiente |
| F06 | Botón flotante de WhatsApp | ⬜ Pendiente |

### 5.2 Fase 2 (post-MVP)

| # | Funcionalidad | Notas |
|---|---|---|
| F07 | Scraping de productos de proveedores | Requiere VPS o cron externo |
| F08 | Integración con MercadoPago | Cuando el cliente lo requiera |
| F09 | Notificaciones por WhatsApp | API WhatsApp Business |
| F10 | Sección de consejos para la construcción | Blog / CPT propio |
| F11 | Calculadora de materiales (ladrillos, arena, cemento) | Shortcode interactivo, genera tráfico SEO |
| F12 | Suscripción de clientes para ofertas especiales | Requiere sistema de email marketing (ej: Mailchimp, o envío propio) |

---

## 6. Flujos principales

### Flujo de presupuesto (MVP)
```
Cliente navega catálogo
    ↓
Agrega productos al carrito (WooCommerce)
    ↓
Va al carrito → click "Solicitar presupuesto"
    ↓
Completa formulario: nombre, email, teléfono, mensaje opcional
    ↓
Se envía email al corralón con el detalle del carrito
    ↓
Se muestra popup de confirmación al cliente ("¡Presupuesto enviado! Te contactaremos a la brevedad")
    ↓
El corralón responde por email o WhatsApp
```

### Flujo de consulta directa (MVP)
```
Cliente completa formulario de consultas
    ↓
Se envía email al corralón
    ↓
Botón flotante WhatsApp disponible en todo momento
```

---

## 7. Decisiones técnicas tomadas

| Decisión | Justificación |
|---|---|
| WooCommerce para catálogo | Evita reinventar gestión de productos, categorías e imágenes |
| Sin pasarela de pagos en MVP | El corralón no está listo operativamente para ventas online |
| Carrito para presupuesto, no para pago | Flujo conocido por el cliente, bajo costo de implementación |
| `ProductoRepositoryInterface` | Permite en Fase 2 agregar `ScraperProductoRepository` sin tocar el servicio |
| PSR-4 con Composer | Autoload profesional, compatible con Linux (case-sensitive) |

---

## 8. Estado actual del desarrollo

### Completado
- Estructura base del plugin con Composer y PSR-4
- Entidad `Producto` (Domain)
- `ProductoRepositoryInterface` (DIP aplicado)
- `ProductoRepository` conectado a `WC_Product_Query`
- Stubs de WordPress e Intelephense configurados

### En curso
- `ProductoService::listarTodos()` — conectar repositorio con servicio

### Próximos pasos inmediatos
1. Completar `ProductoService::listarTodos()`
2. Crear shortcode de prueba para verificar datos reales desde WooCommerce
3. Diseñar flujo de presupuesto (F03)

---

## 9. Deuda técnica conocida

| Item | Descripción | Prioridad |
|---|---|---|
| DT01 | `ProductoService` sin manejo de errores formal | Baja — post MVP |
| DT02 | Sin tests automatizados | Baja — post MVP |

---

## 10. Convenciones de desarrollo

- Namespaces: `RDT\Corralon\...`
- Carpetas en PascalCase (PSR-4, Linux case-sensitive)
- Los repositorios son la única capa que interactúa con WooCommerce o la DB
- Los servicios no conocen WordPress — solo reciben y retornan entidades de dominio
- Commits en rama `develop`, merge a `main` solo cuando está listo para producción