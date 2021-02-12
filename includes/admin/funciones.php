<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Muestra el icono
function apg_free_shipping_icono( $etiqueta, $metodo ) {
	$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $metodo->instance_id .'_settings' ) );
    
	//Previene compatibilidad con WC - APG Weight Shipping
	if ( $metodo->cost > 0 || !isset( $configuracion[ 'precio' ] ) ) {
		return $etiqueta;
	}

    //Añade el precio
	$precio    = ( $configuracion[ 'precio' ] == 'yes' ) ? ': ' . wc_price( $metodo->cost ) : '';
	
	//¿Mostramos el icono?
	if ( !empty( $configuracion[ 'icono' ] ) && @getimagesize( $configuracion[ 'icono' ] ) && $configuracion[ 'muestra_icono' ] != 'no' ) {
		$tamano = @getimagesize( $configuracion[ 'icono' ] );
		$imagen	= '<img class="apg_free_shipping_icon" src="' . $configuracion[ 'icono' ] . '" witdh="' . $tamano[ 0 ] . '" height="' . $tamano[ 1 ] . '" />';
		if ( $configuracion[ 'muestra_icono' ] == 'delante' ) { //Icono delante
			$etiqueta = $imagen . ' ' . $etiqueta . $precio;
		} else if ( $configuracion[ 'muestra_icono' ] == 'detras' ) {
			$etiqueta = $metodo->label . ' ' . $imagen . $precio; //Icono detrás
		} else {
			$etiqueta = $imagen . $precio; //Sólo icono
		}
	} else {
		$etiqueta = $metodo->label . $precio; //Sin icono
	}
	
	//Tiempo de entrega
	if ( !empty( $configuracion[ 'entrega' ] ) ) {
		$etiqueta .= '<br /><small class="apg_free_shipping_delivery">' . sprintf( __( 'Estimated delivery time: %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $configuracion[ 'entrega' ] ) . '</small>';
	}

	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_free_shipping_icono', PHP_INT_MAX, 2 );

//Oculta el resto de gastos de envío
function apg_free_shipping_oculta_envios( $envios ) {
	$envio_gratis = [];

	foreach ( $envios as $clave => $envio ) {
		if ( 'apg_free_shipping' == $envio->method_id || 0 == $envio->cost ) {
			$envio_gratis[ $clave ] = $envio;
		}
	}
 
	return !empty( $envio_gratis ) ? $envio_gratis : $envios;
}
