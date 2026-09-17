/**
 * Script para manejar los bloques de envío de APG en el carrito.
 */
jQuery(function($) {
    // Solo aplica en la página del carrito
    if ( $('body').hasClass('woocommerce-cart') ) {
        // Inyecta el CSS personalizado
        const estiloAPG = `
            .wc-block-components-radio-control__option.wc-block-components-radio-control__option-checked {
                padding-right: 0;
            }
        `;
        const styleTag = document.createElement('style');
        styleTag.type = 'text/css';
        styleTag.appendChild(document.createTextNode(estiloAPG));
        document.head.appendChild(styleTag);
    }
    
    // Escapa un valor que va dentro de un atributo HTML. Un escapador basado en el DOM
    // (textContent / .text()) no codifica las comillas y no sirve en esta posición.
    function escAttr( valor ) {
        if ( valor === null || typeof valor === 'undefined' ) {
            return '';
        }

        return String( valor )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' )
            .replace( /'/g, '&#039;' );
    }

    function actualizarIconosAPG() {
		$('.wc-block-components-shipping-rates-control input[type="radio"][name^="radio-control-"]').each(function() {
			const $input = $(this);
			const valor  = $input.val();
			const $label = $input.closest('label').find('.wc-block-components-radio-control__label');

            if ( ! valor || $label.attr('data-apg-cargado') === '1' ) return;
            
            $label.attr('data-apg-cargado', '1');

			$.post(apg_shipping.ajax_url, {
				action: 'apg_free_shipping_ajax_datos',
				metodo: valor
			}, function(res) {
				if ( ! res.success || ! res.data ) return;

				const d = res.data;
				let icono = '', entrega = '', html = '';

				if ( d.icono && d.muestra !== 'no' ) {
					icono = `<img src="${escAttr( d.icono )}" style="display:inline;" class="apg_icon">`;
				}

				if ( d.muestra === 'delante' ) {
					html = icono + " " + d.titulo;
				} else if ( d.muestra === 'detras' ) {
					html = d.titulo + " " + icono;
				} else if ( d.muestra === 'solo' ) {
					html = icono;
				} else {
					html = d.titulo;
				}

				if ( d.entrega ) {
					html += `<br><small class="apg_shipping_delivery">${d.entrega}</small>`;
				}

				$label.html(html);
			});
		});
	}

	// Ejecuta en carga inicial + si cambian elementos
	const observer = new MutationObserver(actualizarIconosAPG);
	observer.observe(document.body, { childList: true, subtree: true });

	actualizarIconosAPG();      
});