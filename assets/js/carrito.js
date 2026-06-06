(function () {
    'use strict';

    window.rdtCarrito = {
        agregarAlCarrito: function (id, btn, cartNonce, cartUrl) {
            var textoOriginal = btn.textContent;
            btn.disabled    = true;
            btn.textContent = 'Agregando…';

            fetch('/wp-json/wc/store/v1/cart/add-item', {
                method:      'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Nonce':        cartNonce,
                },
                body: JSON.stringify({ id: id, quantity: 1 }),
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                if (cartUrl) {
                    window.location.href = cartUrl;
                } else {
                    btn.textContent = '✓ Agregado';
                    setTimeout(function () {
                        btn.disabled    = false;
                        btn.textContent = textoOriginal;
                    }, 2000);
                }
            })
            .catch(function (err) {
                console.error('[rdtCarrito] Error al agregar al carrito:', err);
                btn.disabled    = false;
                btn.textContent = textoOriginal;
            });
        },
    };
})();
