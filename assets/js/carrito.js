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
                window.location.href = cartUrl;
            })
            .catch(function (err) {
                console.error('[rdtCarrito] Error al agregar al carrito:', err);
                btn.disabled    = false;
                btn.textContent = textoOriginal;
            });
        },
    };
})();
