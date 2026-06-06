(function () {
    'use strict';

    // Animación del modal
    var s = document.createElement('style');
    s.textContent = '@keyframes rdt-fadein{from{opacity:0}to{opacity:1}}';
    document.head.appendChild(s);

    var cfg = window.rdtHome || {};

    // -------------------------------------------------------- Utilidades URL

    function irA(base, params) {
        var qs = new URLSearchParams(params).toString();
        window.location.href = base + (qs ? '?' + qs : '');
    }

    // -------------------------------------------------------- Búsqueda

    var searchInput  = document.querySelector('.rdt-home__search input');
    var searchButton = document.querySelector('.rdt-home__search button');

    function ejecutarBusqueda() {
        if (!searchInput) return;
        var termino = searchInput.value.trim();
        if (!termino) return;
        irA(cfg.catalog_url || '/catalogo/', { buscar: termino });
    }

    if (searchButton) {
        searchButton.addEventListener('click', ejecutarBusqueda);
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') ejecutarBusqueda();
        });
    }

    // -------------------------------------------------------- Barra de categorías (nav)

    var catItems = document.querySelectorAll('.rdt-home__cat-nav-item');
    catItems.forEach(function (btn) {
        btn.addEventListener('click', function () {
            catItems.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            var slug = btn.dataset.cat || '';
            if (slug === 'all' || slug === '') {
                irA(cfg.catalog_url || '/catalogo/', {});
            } else {
                irA(cfg.catalog_url || '/catalogo/', { categoria: slug });
            }
        });
    });

    // -------------------------------------------------------- Grilla de categorías

    var catCards = document.querySelectorAll('.rdt-home__cat-card[data-cat]');
    catCards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            var slug = card.dataset.cat || '';
            if (!slug) return; // deja que el <a> navegue normalmente
            e.preventDefault();
            irA(cfg.catalog_url || '/catalogo/', { categoria: slug });
        });
    });

    // -------------------------------------------------------- Cart badge

    function updateCartBadge() {
        fetch('/wp-json/wc/store/v1/cart', {
            credentials: 'same-origin',
            headers: cfg.cart_nonce ? { 'Nonce': cfg.cart_nonce } : {},
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;
            var count = data.items_count || 0;
            var badge = document.getElementById('rdt-home-cart-count');
            if (!badge) return;
            badge.textContent = count;
            if (count > 0) {
                badge.removeAttribute('hidden');
            } else {
                badge.setAttribute('hidden', '');
            }
        })
        .catch(function () {});
    }

    // -------------------------------------------------------- Modal de confirmación

    function mostrarModalSuscripcion() {
        var overlay = document.createElement('div');
        overlay.id = 'rdt-modal-suscripcion';
        overlay.style.cssText = [
            'position:fixed', 'inset:0', 'z-index:9999',
            'background:rgba(0,0,0,0.55)',
            'display:flex', 'align-items:center', 'justify-content:center',
            'animation:rdt-fadein 0.2s ease'
        ].join(';');

        var box = document.createElement('div');
        box.style.cssText = [
            'background:#fff', 'border-radius:12px',
            'padding:40px 32px', 'max-width:420px', 'width:90%',
            'text-align:center', 'box-shadow:0 8px 40px rgba(0,0,0,0.18)',
            'position:relative'
        ].join(';');

        box.innerHTML = [
            '<div style="font-size:48px;margin-bottom:12px">✉️</div>',
            '<h3 style="margin:0 0 8px;font-size:1.4rem;color:#1C2B3A">',
            '¡Suscripto!</h3>',
            '<p style="margin:0 0 24px;color:#5A6A78;font-size:0.95rem">',
            'Revisá tu correo para confirmar tu suscripción.</p>',
            '<button id="rdt-modal-cerrar" style="',
            'background:#3A8FD4;color:#fff;border:none;border-radius:6px;',
            'padding:10px 28px;font-size:1rem;cursor:pointer;font-weight:600',
            '">Cerrar</button>'
        ].join('');

        overlay.appendChild(box);
        document.body.appendChild(overlay);

        function cerrar() {
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }

        document.getElementById('rdt-modal-cerrar').addEventListener('click', cerrar);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) cerrar();
        });
        document.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Escape') { cerrar(); document.removeEventListener('keydown', onKey); }
        });
    }

    // -------------------------------------------------------- Formulario de suscripción

    var form = document.querySelector('.rdt-home__suscripcion-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('input[type="email"]');
            var btn   = form.querySelector('button[type="submit"]');
            if (!input || !btn) return;

            var val   = input.value.trim();
            var valid = val.length > 0 && val.indexOf('@') > 0 && val.indexOf('.') > 0;

            if (!valid) {
                input.classList.add('rdt-input-error');
                input.focus();
                return;
            }

            input.classList.remove('rdt-input-error');
            btn.disabled    = true;
            btn.textContent = 'Enviando...';

            fetch(cfg.rest_url_suscripcion || '', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce':   cfg.nonce || '',
                },
                body: JSON.stringify({ email: val }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    input.value     = '';
                    btn.textContent = 'Suscribirme';
                    btn.disabled    = false;
                    mostrarModalSuscripcion();
                } else {
                    btn.disabled    = false;
                    btn.textContent = 'Suscribirme';
                    input.classList.add('rdt-input-error');
                }
            })
            .catch(function () {
                btn.disabled    = false;
                btn.textContent = 'Suscribirme';
            });
        });

        var emailInput = form.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.addEventListener('input', function () {
                this.classList.remove('rdt-input-error');
            });
        }
    }

    // -------------------------------------------------------- Scroll al banner

    var btnOfertas = document.querySelector('.js-scroll-suscripcion');
    if (btnOfertas) {
        btnOfertas.addEventListener('click', function () {
            var banner = document.getElementById('rdt-home-suscripcion');
            if (banner) {
                banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
                var input = banner.querySelector('input[type="email"]');
                if (input) setTimeout(function () { input.focus(); }, 400);
            }
        });
    }

    // -------------------------------------------------------- Init

    updateCartBadge();
})();
