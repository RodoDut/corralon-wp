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

    // Cerrar modal al hacer clic en overlay o en el botón de cierre
    $(document).on('click', '#rdt-modal-close', function () {
        $('#rdt-modal-overlay').fadeOut(200);
    });

    $(document).on('click', '#rdt-modal-overlay', function (e) {
        if (e.target === this) {
            $(this).fadeOut(200);
        }
    });

    // Cerrar con Escape
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

        $.ajax({
            url:    rdtPresupuesto.apiUrl,
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rdtPresupuesto.nonce);
            },
            data: {
                nombre:   $form.find('[name=nombre]').val(),
                email:    $form.find('[name=email]').val(),
                telefono: $form.find('[name=telefono]').val(),
                mensaje:  $form.find('[name=mensaje]').val(),
            },
            success: function (response) {
                if (response.success) {
                    $('#rdt-modal-overlay').fadeOut(200);
                    $form[0].reset();
                    mostrarConfirmacion();
                } else {
                    $error.text(response.message || 'Error al enviar.').show();
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Ocurrió un error inesperado. Intentá nuevamente.';
                $error.text(msg).show();
            },
            complete: function () {
                $btn.prop('disabled', false).text('Enviar presupuesto');
            }
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
