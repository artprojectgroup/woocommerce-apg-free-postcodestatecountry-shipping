<?php
//Muestra el icono
function apg_free_shipping_icono( $etiqueta, $metodo ) {
	$gasto_de_envio	= explode( ":", $etiqueta );
	$id = explode( ":", $metodo->id );
	$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $id[1] .'_settings' ) );
	//¿Mostramos el icono?
	if ( !empty( $configuracion['icono'] ) && @getimagesize( $configuracion['icono'] ) && $configuracion['muestra_icono'] != 'no' ) {
		$tamano = @getimagesize( $configuracion['icono'] );
		$imagen	= '<img class="apg_free_shipping_icon" src="' . $configuracion['icono'] . '" witdh="' . $tamano[0] . '" height="' . $tamano[1] . '" />';
		if ( $configuracion['muestra_icono'] == 'delante' ) {
			$etiqueta = $imagen . ' ' . $etiqueta;
		} else if ( $configuracion['muestra_icono'] == 'detras' ) {
			$etiqueta = $gasto_de_envio[0] . ' ' . $imagen . ':' . $gasto_de_envio[1]; //Icono detrás
		} else {
			$etiqueta = $imagen . ':' . $gasto_de_envio[1]; //Sólo icono
		}
	}
	//Tiempo de entrega
	if ( !empty( $configuracion['entrega'] ) ) {
		$etiqueta .= '<br /><small class="apg_free_shipping_delivery">' . sprintf( __( 'Estimated delivery time: %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $configuracion['entrega'] ) . '</small>';
	}

	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_free_shipping_icono', PHP_INT_MAX, 2 );

//Oculta el resto de gastos de envío
function apg_free_shipping_oculta_envios( $envios ) {
	$envio_gratis = array();

	foreach ( $envios as $clave => $envio ) {
		if ( 'apg_free_shipping' === $envio->method_id || 0 == $envio->cost ) {
			$envio_gratis[ $clave ] = $envio;
		}
	}
 
	return !empty( $envio_gratis ) ? $envio_gratis : $envios;
}
