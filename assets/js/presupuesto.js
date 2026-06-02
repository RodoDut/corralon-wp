(function ($) {
    'use strict';

    var $overlay = $(
        '<div id="rdt-modal-overlay">' +
            '<div id="rdt-modal">' +
                '<button type="button" id="rdt-modal-close" aria-label="Cerrar">&times;</button>' +
                '<h2>Solicitar presupuesto</h2>' +
                '<form id="rdt-presupuesto-form" novalidate>' +
                    '<label>Nombre <span aria-hidden="true">*</span>' +
                        '<input type="text" name="nombre" required autocomplete="name">' +
                    '</label>' +
                    '<label>Email <span aria-hidden="true">*</span>' +
                        '<input type="email" name="email" required autocomplete="email">' +
                    '</label>' +
                    '<label>Teléfono <span aria-hidden="true">*</span>' +
                        '<input type="tel" name="telefono" required autocomplete="tel">' +
                    '</label>' +
                    '<label>Mensaje' +
                        '<textarea name="mensaje" rows="4"></textarea>' +
                    '</label>' +
                    '<p id="rdt-form-error" role="alert"></p>' +
                    '<button type="submit" class="button alt">Enviar presupuesto</button>' +
                '</form>' +
            '</div>' +
        '</div>'
    );

    $('body').append($overlay);

    // Abrir modal
    $(document).on('click', '#rdt-solicitar-presupuesto', function () {
        $('#rdt-modal-overlay').fadeIn(200);
        $('#rdt-presupuesto-form [name=nombre]').trigger('focus');
    });

    // Cerrar modal
    $(document).on('click', '#rdt-modal-close', function () {
        $('#rdt-modal-overlay').fadeOut(200);
    });

    $(document).on('click', '#rdt-modal-overlay', function (e) {
        if (e.target === this) {
            $(this).fadeOut(200);
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#rdt-modal-overlay').is(':visible')) {
            $('#rdt-modal-overlay').fadeOut(200);
        }
    });

    // Envío del formulario
    $(document).on('submit', '#rdt-presupuesto-form', function (e) {
        e.preventDefault();

        var $form  = $(this);
        var $btn   = $form.find('[type=submit]');
        var $error = $('#rdt-form-error');

        $error.text('').hide();
        $btn.prop('disabled', true).text('Enviando…');

        // Primero obtenemos el carrito desde el Store API de WooCommerce,
        // luego enviamos esos datos junto con el formulario.
        fetch('/wp-json/wc/store/v1/cart', {
            credentials: 'same-origin',
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (cart) {
            var lineas = (cart.items || []).map(function (item) {
                return {
                    nombre:          item.name,
                    cantidad:        item.quantity,
                    precio_unitario: parseFloat(item.prices.price) / 100,
                };
            });

            return $.ajax({
                url:    rdtPresupuesto.apiUrl,
                method: 'POST',
                contentType: 'application/json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', rdtPresupuesto.nonce);
                },
                data: JSON.stringify({
                    nombre:   $form.find('[name=nombre]').val(),
                    email:    $form.find('[name=email]').val(),
                    telefono: $form.find('[name=telefono]').val(),
                    mensaje:  $form.find('[name=mensaje]').val(),
                    lineas:   lineas,
                }),
            });
        })
        .then(function (response) {
            if (response.success) {
                $('#rdt-modal-overlay').fadeOut(200);
                $form[0].reset();
                mostrarConfirmacion();
            } else {
                $error.text(response.message || 'Error al enviar.').show();
            }
        })
        .catch(function (err) {
            console.error('[rdtPresupuesto] Error:', err);
            $error.text('Ocurrió un error inesperado. Intentá nuevamente.').show();
        })
        .finally(function () {
            $btn.prop('disabled', false).text('Enviar presupuesto');
        });
    });

    function mostrarConfirmacion() {
        var $popup = $(
            '<div id="rdt-confirmacion" role="status">' +
                '¡Presupuesto enviado! Te contactaremos a la brevedad.' +
            '</div>'
        );
        $('body').append($popup);

        setTimeout(function () {
            $popup.fadeOut(400, function () { $(this).remove(); });
        }, 4500);
    }

})(jQuery);
