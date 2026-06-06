(function () {
    'use strict';

    var cfg = window.rdtDetalle;
    if (!cfg) return;

    var articulo = document.querySelector('.rdt-detalle[data-product-id]');
    if (!articulo) return;

    var productId  = Number(articulo.dataset.productId);
    var categoria  = articulo.dataset.categoria || '';

    // ---------------------------------------------------------------- escHtml

    function escHtml(val) {
        return String(val)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // -------------------------------------------------------------- Tarjeta (relacionados)

    function renderTarjeta(p) {
        var imgHtml = p.imagen_url
            ? '<img src="' + escHtml(p.imagen_url) + '" alt="' + escHtml(p.nombre) + '" loading="lazy">'
            : '<div class="catalogo-card__img-placeholder"></div>';

        var badgeHtml = p.categorias.length > 0
            ? '<span class="catalogo-card__badge">' + escHtml(p.categorias[0].nombre) + '</span>'
            : '';

        var enlace = p.permalink ? escHtml(p.permalink) : '#';

        var card = document.createElement('article');
        card.className = 'catalogo-card';
        card.innerHTML =
            '<a href="' + enlace + '" class="catalogo-card__link">' +
                '<div class="catalogo-card__img">' + imgHtml + '</div>' +
            '</a>' +
            '<div class="catalogo-card__body">' +
                '<a href="' + enlace + '" class="catalogo-card__link">' +
                    '<h3 class="catalogo-card__nombre">' + escHtml(p.nombre) + '</h3>' +
                '</a>' +
                badgeHtml +
                '<!-- FASE 2: precio -->' +
                '<button type="button"' +
                    ' class="button catalogo-card__btn-presupuesto"' +
                    ' data-id="' + escHtml(String(p.id)) + '">' +
                    'Solicitar presupuesto' +
                '</button>' +
            '</div>';

        return card;
    }

    // --------------------------------------------------------- Relacionados

    function cargarRelacionados() {
        var grilla = document.getElementById('rdt-relacionados-grilla');
        if (!grilla) return;

        var params = new URLSearchParams({ por_pagina: '5', pagina: '1' });
        if (categoria) {
            params.set('categoria', categoria);
        }

        fetch(cfg.rest_url + '?' + params.toString(), {
            headers: { 'X-WP-Nonce': cfg.nonce },
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            var relacionados = data.productos
                .filter(function (p) { return p.id !== productId; })
                .slice(0, 4);

            relacionados.forEach(function (p) {
                grilla.appendChild(renderTarjeta(p));
            });
        })
        .catch(function (err) {
            console.error('[rdtDetalle] Error al cargar relacionados:', err);
        });
    }

    // ----------------------------------------- Botón principal

    var btnPrincipal = articulo.querySelector('.rdt-detalle__btn-presupuesto');
    if (btnPrincipal) {
        btnPrincipal.addEventListener('click', function () {
            if (!btnPrincipal.disabled) {
                window.rdtCarrito.agregarAlCarrito(productId, btnPrincipal, cfg.cart_nonce, cfg.cart_url);
            }
        });
    }

    // ----------------------------------------- Botón agregar al carrito

    var btnCarrito = articulo.querySelector('.rdt-detalle__btn-carrito');
    if (btnCarrito) {
        btnCarrito.addEventListener('click', function () {
            if (!btnCarrito.disabled) {
                window.rdtCarrito.agregarAlCarrito(productId, btnCarrito, cfg.cart_nonce, null);
            }
        });
    }

    // ----------------------------------------- Botones en relacionados

    var grillaRelacionados = document.getElementById('rdt-relacionados-grilla');
    if (grillaRelacionados) {
        grillaRelacionados.addEventListener('click', function (e) {
            var btn = e.target.closest('.catalogo-card__btn-presupuesto');
            if (btn && !btn.disabled) {
                window.rdtCarrito.agregarAlCarrito(Number(btn.dataset.id), btn, cfg.cart_nonce, cfg.cart_url);
            }
        });
    }

    // -------------------------------------------------------------------Init

    cargarRelacionados();
})();
