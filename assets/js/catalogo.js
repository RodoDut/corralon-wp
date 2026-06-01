(function () {
    'use strict';

    var cfg = window.rdtCatalogo;
    if (!cfg) return;

    var state = {
        pagina:    1,
        categoria: '',
        cargando:  false,
        hayMas:    true,
    };

    var root     = document.getElementById('catalogo-root');
    var tabsEl   = document.getElementById('catalogo-tabs');
    var grillaEl = document.getElementById('catalogo-grilla');
    var spinner  = document.getElementById('catalogo-spinner');
    var sentinel = document.getElementById('catalogo-sentinel');

    if (!root || !tabsEl || !grillaEl || !spinner || !sentinel) return;

    // ------------------------------------------------------------------ Tabs

    function renderTabs() {
        tabsEl.innerHTML = '';

        var items = [{ slug: '', nombre: 'Todos' }].concat(cfg.categorias);

        items.forEach(function (cat) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'catalogo-tab' + (cat.slug === state.categoria ? ' active' : '');
            btn.textContent = cat.nombre;
            btn.dataset.slug = cat.slug;
            btn.setAttribute('role', 'tab');
            btn.setAttribute('aria-selected', cat.slug === state.categoria ? 'true' : 'false');
            tabsEl.appendChild(btn);
        });
    }

    tabsEl.addEventListener('click', function (e) {
        var btn = e.target.closest('.catalogo-tab');
        if (!btn) return;

        state.categoria = btn.dataset.slug;
        state.pagina    = 1;
        state.hayMas    = true;
        grillaEl.innerHTML = '';
        sentinel.hidden    = true;

        renderTabs();
        cargar();
    });

    // ------------------------------------------------------------------ Fetch

    function cargar() {
        if (state.cargando || !state.hayMas) return;

        state.cargando  = true;
        sentinel.hidden = true;
        spinner.hidden  = false;

        var params = new URLSearchParams({
            pagina:     String(state.pagina),
            por_pagina: String(cfg.por_pagina),
        });
        if (state.categoria) {
            params.set('categoria', state.categoria);
        }

        fetch(cfg.rest_url + '?' + params.toString(), {
            headers: { 'X-WP-Nonce': cfg.nonce },
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            renderProductos(data.productos);
            state.pagina++;
            state.hayMas = state.pagina <= data.paginas;
            sentinel.hidden = !state.hayMas;
        })
        .catch(function (err) {
            console.error('[rdtCatalogo] Error al cargar productos:', err);
        })
        .finally(function () {
            state.cargando = false;
            spinner.hidden = true;
        });
    }

    // ---------------------------------------------------------------- Render

    function escHtml(val) {
        return String(val)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderProductos(productos) {
        productos.forEach(function (p) {
            var primCat = p.categorias.length > 0 ? p.categorias[0].nombre : '';

            var imgHtml = p.imagen_url
                ? '<img src="' + escHtml(p.imagen_url) + '" alt="' + escHtml(p.nombre) + '" loading="lazy">'
                : '<div class="catalogo-card__img-placeholder"></div>';

            var badgeHtml = primCat
                ? '<span class="catalogo-card__badge">' + escHtml(primCat) + '</span>'
                : '';

            var card = document.createElement('article');
            card.className = 'catalogo-card';
            card.innerHTML =
                '<a href="' + escHtml(p.permalink) + '" class="catalogo-card__link">' +
                    '<div class="catalogo-card__img">' + imgHtml + '</div>' +
                '</a>' +
                '<div class="catalogo-card__body">' +
                    '<a href="' + escHtml(p.permalink) + '" class="catalogo-card__link">' +
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

            grillaEl.appendChild(card);
        });
    }

    // --------------------------------------------------------- Agregar carrito

    grillaEl.addEventListener('click', function (e) {
        var btn = e.target.closest('.catalogo-card__btn-presupuesto');
        if (!btn || btn.disabled) return;

        window.rdtCarrito.agregarAlCarrito(Number(btn.dataset.id), btn, cfg.cart_nonce, cfg.cart_url);
    });

    // ---------------------------------------------------- IntersectionObserver

    var observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
            cargar();
        }
    }, { rootMargin: '200px' });

    observer.observe(sentinel);

    // -------------------------------------------------------------------Init

    renderTabs();
    cargar();
})();
