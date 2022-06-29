<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Muestra el icono
function apg_free_shipping_icono( $etiqueta, $metodo ) {
	$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $metodo->instance_id .'_settings' ) );
    
	//Previene compatibilidad con WC - APG Weight Shipping
	if ( $metodo->cost > 0 || ! isset( $configuracion[ 'precio' ] ) ) {
		return $etiqueta;
	}

    //Añade el precio
	$precio    = ( $configuracion[ 'precio' ] == 'yes' ) ? ': ' . wc_price( $metodo->cost ) : '';
	
	//¿Mostramos el icono?
	if ( ! empty( $configuracion[ 'icono' ] ) && @getimagesize( $configuracion[ 'icono' ] ) && $configuracion[ 'muestra_icono' ] != 'no' ) {
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
	if ( ! empty( $configuracion[ 'entrega' ] ) ) {
        $etiqueta .= ( apply_filters( 'apg_free_shipping_delivery', true ) ) ? '<br /><small class="apg_free_shipping_delivery">' . sprintf( __( 'Estimated delivery time: %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $configuracion[ 'entrega' ] ) . '</small>' : '<br /><small class="apg_free_shipping_delivery">' . $configuracion[ 'entrega' ] . '</small>';
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
 
	return ! empty( $envio_gratis ) ? $envio_gratis : $envios;
}

//Añade APG Shipping a WooCommerce
function apg_free_shipping_anade_gastos_de_envio( $methods ) {
    $methods[ 'apg_free_shipping' ] = 'WC_apg_free_shipping';

    return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_anade_gastos_de_envio' );

//Filtra los medios de pago
function apg_free_shipping_filtra_medios_de_pago( $medios ) {
    if ( isset( WC()->session->chosen_shipping_methods ) ) {
        $id = explode( ":", WC()->session->chosen_shipping_methods[ 0 ] );
    } else if ( isset( $_POST[ 'shipping_method' ] ) ) {
        $id = explode( ":", $_POST[ 'shipping_method' ][ 0 ] );
    }
    if ( ! isset( $id[ 1 ] ) ) {
        return $medios;
    }
    $configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $id[ 1 ] .'_settings' ) );

    if ( isset( $_POST[ 'payment_method' ] ) && !$medios ) {
        $medios = $_POST[ 'payment_method' ];
    }

    if ( ! empty( $configuracion[ 'pago' ] ) && $configuracion[ 'pago' ][ 0 ] != 'todos' ) {
        foreach ( $medios as $nombre => $medio ) {
            if ( is_array( $configuracion[ 'pago' ] ) ) {
                if ( ! in_array( $nombre, $configuracion[ 'pago' ] ) ) {
                    unset( $medios[ $nombre ] );
                }
            } else { 
                if ( $nombre != $configuracion[ 'pago' ] ) {
                    unset( $medios[ $nombre ] );
                }
            }
        }
    }

    return $medios;
}
add_filter( 'woocommerce_available_payment_gateways', 'apg_free_shipping_filtra_medios_de_pago' );

//Actualiza los medios de pago y las zonas de envío
function apg_free_shipping_toma_de_datos() {
	global $zonas_de_envio;
	
    $zonas_de_envio    = WC_Shipping_Zones::get_zones(); //Guardamos las zonas de envío
}
if ( strpos( $_SERVER[ 'REQUEST_URI' ], 'wc-settings&tab=shipping&instance_id' ) !== false ) {
    add_action( 'admin_init', 'apg_free_shipping_toma_de_datos' );
}

//Gestiona los gastos de envío
function apg_free_shipping_gestiona_envios( $envios ) {
    if ( isset( WC()->session->chosen_shipping_methods ) ) {
        $id = explode( ":", WC()->session->chosen_shipping_methods[ 0 ] );
    } else if ( isset( $_POST[ 'shipping_method' ][ 0 ] ) ) {
        $id = explode( ":", $_POST[ 'shipping_method' ][ 0 ] );
    }
    if ( ! isset( $id[ 1 ] ) ) {
        return $envios;
    }
    $configuracion  = maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $id[ 1 ] . '_settings' ) );

    if ( isset( $configuracion[ 'envio' ] ) && ! empty( $configuracion[ 'envio' ] ) ) {
        foreach ( $envios[ 0 ][ 'rates' ] as $clave => $envio ) {
            foreach( $configuracion[ 'envio' ] as $metodo ) {
                if ( $metodo != 'todos' ) {
                    if ( ( $metodo == 'ninguno' && $id[ 1 ] != $envio->instance_id ) || ( ! in_array( $envio->instance_id, $configuracion[ 'envio' ] ) && $id[ 1 ] != $envio->instance_id ) ) {
                        unset( $envios[ 0 ][ 'rates' ][ $clave ] );
                    }
                }
            }
        }
    }
    
    return $envios;
}
add_filter( 'woocommerce_shipping_packages', 'apg_free_shipping_gestiona_envios', 20, 1 );