<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Muestra el icono
function apg_free_shipping_icono( $etiqueta, $metodo ) {
    //Variables
    $instance_id = $metodo->instance_id;
    $cache_key   = "apg_shipping_icono_{$instance_id}";
    
    //Obtiene configuración del método de envío
    $opcion_bruta               = get_option( "woocommerce_apg_free_shipping_{$instance_id}_settings" );
    $apg_free_shipping_settings = is_array( $opcion_bruta ) ? $opcion_bruta : maybe_unserialize( $opcion_bruta );
    
    //Previene compatibilidad con WC - APG Weight Shipping
    if ( ! is_array( $apg_free_shipping_settings ) || ! isset( $metodo->cost ) || $metodo->cost > 0 || ! isset( $apg_free_shipping_settings[ 'precio' ] ) ) {
		return $etiqueta;
	}

    //Usa etiqueta en caché
    $etiqueta   = get_transient( $cache_key );
    if ( false !== $etiqueta ) {
        return $etiqueta;
    }

    //¿Mostramos el icono?
    $icon_url       = $apg_free_shipping_settings[ 'icono' ] ?? '';
    $mostrar_icono  = $apg_free_shipping_settings[ 'muestra_icono' ] ?? '';
    if ( ! empty( $icon_url ) && filter_var( $icon_url, FILTER_VALIDATE_URL ) && $mostrar_icono !== 'no' ) {
        //Añade el precio
        $precio = ( $apg_free_shipping_settings['precio'] === 'yes' ) ? ': ' . wc_price( $metodo->cost ) : '';
        
        //Procesa imagen y obtiene su tamaño
        require_once ABSPATH . 'wp-admin/includes/file.php'; // Asegura que download_url() existe
        $ancho  = null;
        $alto   = null;
        $icon   = download_url( $icon_url );
        if ( ! is_wp_error( $icon ) ) {
            $tamano = wp_getimagesize( $icon );
            if ( is_array( $tamano ) ) {
                list( $ancho, $alto )   = $tamano;
            }
            wp_delete_file( $icon );
        }
        
        //Construcción de la etiqueta <img>
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image
        $imagen = '<img class="apg_free_shipping_icon apg_icon" src="' . esc_url( $icon_url ) . '"';
        $imagen .= $ancho ? ' width="' . intval( $ancho ) . '"' : '';
        $imagen .= $alto  ? ' height="' . intval( $alto ) . '"' : '';
        $imagen .= ' style="display:inline;" />';

        $titulo  = apply_filters( 'apg_free_shipping_label', $metodo->label );
        if ( $mostrar_icono === 'delante' ) {
            $etiqueta   = $imagen . ' ' . $titulo . $precio; //Icono delante
        } else if ( $mostrar_icono === 'detras' ) {
            $etiqueta   = $titulo . ' ' . $imagen . $precio; //Icono detrás
        } else {
            $etiqueta   = $imagen . $precio; //Sólo icono
        }
    } else {
        $etiqueta   = apply_filters( 'apg_free_shipping_label', $metodo->label ) . $precio; //Sin icono y con precio
    }
	
	//Tiempo de entrega
	if ( ! empty( $apg_free_shipping_settings[ 'entrega' ] ) ) {
        // translators: %s is the estimated delivery time (e.g., "24-48 hours").
        $etiqueta .= ( apply_filters( 'apg_free_shipping_delivery', true ) ) ? '<br /><small class="apg_free_shipping_delivery">' . sprintf( __( 'Estimated delivery time: %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), esc_html( $apg_free_shipping_settings[ 'entrega' ] ) ) . '</small>' : '<br /><small class="apg_free_shipping_delivery">' . esc_html( $apg_free_shipping_settings[ 'entrega' ] ) . '</small>';
	}

    //Guarda en caché durante una hora
    set_transient( $cache_key, $etiqueta, HOUR_IN_SECONDS );

	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_free_shipping_icono', PHP_INT_MAX, 2 );

//Oculta el resto de gastos de envío
function apg_free_shipping_oculta_envios( $envios ) {
	$envio_gratis = [];

	foreach ( $envios as $clave => $envio ) {
        if ( 'apg_free_shipping' === $envio->method_id || ( isset( $envio->cost ) && floatval( $envio->cost ) === 0.0 ) ) {
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
    $apg_free_shipping_settings	= apg_free_shipping_dame_configuracion();

    if ( ! empty( $apg_free_shipping_settings[ 'pago' ] ) && $apg_free_shipping_settings[ 'pago' ][ 0 ] !== 'todos' ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST[ 'payment_method' ] ) && ! $medios ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $medios = sanitize_text_field( wp_unslash( $_POST[ 'payment_method' ] ) );
        }
        foreach ( $medios as $nombre => $medio ) {
            if ( is_array( $apg_free_shipping_settings[ 'pago' ] ) ) {
				if ( ! in_array( $nombre, $apg_free_shipping_settings[ 'pago' ], true ) ) {
                    unset( $medios[ $nombre ] );
                }
            } else { 
                if ( $nombre !== $apg_free_shipping_settings[ 'pago' ] ) {
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
    
    $zonas_de_envio    = get_transient( 'apg_shipping_zonas_de_envio' ); //Obtiene las zonas de envío
    if ( false === $zonas_de_envio ) {
        $zonas_de_envio = WC_Shipping_Zones::get_zones();
		set_transient( 'apg_shipping_zonas_de_envio', $zonas_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
	}
}
if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ), 'wc-settings&tab=shipping' ) !== false ) {
    add_action( 'admin_init', 'apg_free_shipping_toma_de_datos' );
}

//Gestiona los gastos de envío
function apg_free_shipping_gestiona_envios( $envios ) {
    $apg_free_shipping_settings  = apg_free_shipping_dame_configuracion();
    
    if ( isset( $apg_free_shipping_settings[ 'envio' ] ) && is_array( $apg_free_shipping_settings['envio'] ) && ! empty( $apg_free_shipping_settings[ 'envio' ] ) ) {
        if ( isset( $envios[ 0 ] ) && is_array( $envios[ 0 ] ) && isset( $envios[ 0 ][ 'rates' ] ) ) {
            foreach ( $envios[ 0 ][ 'rates' ] as $clave => $envio ) {
                $instance_id    = $envio->instance_id;
                
                foreach( $apg_free_shipping_settings[ 'envio' ] as $metodo ) {
                    if ( $metodo !== 'todos' ) {
                        if ( $metodo === 'ninguno' || ! in_array( $instance_id, $apg_free_shipping_settings['envio'], true ) ) {
                            unset( $envios[ 0 ][ 'rates' ][ $clave ] );
                        }
                    }
                }
            }
        }
    }
    
    return $envios;
}
add_filter( 'woocommerce_shipping_packages', 'apg_free_shipping_gestiona_envios', 20, 1 );
add_filter( 'woocommerce_cart_shipping_packages', 'apg_free_shipping_gestiona_envios', 20, 1 );

//Devuelve la configuración del método de envío
function apg_free_shipping_dame_configuracion() {
    //Corrección propuesta por @rabbitshavefangs en https://wordpress.org/support/topic/problem-in-line-50-of-functiones-php/
    if ( isset( WC()->session ) && is_object( WC()->session ) ) {
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        if ( ! empty( $chosen_shipping_methods ) ) {
            $id = explode( ":", $chosen_shipping_methods[ 0 ] );
        }
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    } elseif ( isset( $_POST[ 'shipping_method' ][ 0 ] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $id = explode( ":", sanitize_text_field( wp_unslash( $_POST[ 'shipping_method' ][ 0 ] ) ) );
    } else {
        return;
    }
    
    return ( !empty( $id[ 1 ] ) ) ? get_option( 'woocommerce_apg_free_shipping_' . $id[ 1 ] . '_settings' ) : [];
}

//Limpia la caché de los iconos
function apg_free_shipping_borra_cache_icono_dinamico( $option, $old_value, $value ) {
	if ( strpos( $option, 'woocommerce_apg_free_shipping_' ) === 0 && strpos( $option, '_settings' ) !== false ) {
		//Extrae el instance_id desde la opción
		if ( preg_match( '/woocommerce_apg_free_shipping_(\d+)_settings/', $option, $matches ) ) {
			$instance_id = $matches[ 1 ];
			$cache_key   = "apg_shipping_icono_{$instance_id}";
			delete_transient( $cache_key );
		}
	}
}
add_action( 'updated_option', 'apg_free_shipping_borra_cache_icono_dinamico', 10, 3 );

//Limpia la caché de taxonomías
function apg_free_shipping_borra_cache_taxonomias_producto( $term_id, $tt_id, $taxonomy ) {
	if ( in_array( $taxonomy, [ 'product_cat', 'product_tag' ], true ) ) {
		delete_transient( 'apg_free_shipping_' . $taxonomy );
	}
}
add_action( 'edited_term', 'apg_free_shipping_borra_cache_taxonomias_producto', 10, 3 );
add_action( 'delete_term', 'apg_free_shipping_borra_cache_taxonomias_producto', 10, 3 );

//Limpia la caché de clases de envío
function apg_free_shipping_borra_cache_clases_envio() {
	delete_transient( 'apg_free_shipping_clases_envio' );
}
add_action( 'woocommerce_shipping_classes_save_class', 'apg_free_shipping_borra_cache_clases_envio' );
add_action( 'woocommerce_shipping_classes_delete_class', 'apg_free_shipping_borra_cache_clases_envio' );

//Limpia la caché de roles
function apg_free_shipping_borra_cache_roles_usuario() {
	delete_transient( 'apg_free_shipping_roles_usuario' );
}
add_action( 'profile_update', 'apg_free_shipping_borra_cache_roles_usuario' );
add_action( 'user_register', 'apg_free_shipping_borra_cache_roles_usuario' );

//Limpia la caché de métodos de pago
function apg_free_shipping_borra_cache_metodos_pago() {
	delete_transient( 'apg_free_shipping_payment_gateways' );
	delete_transient( 'apg_free_shipping_metodos_pago' );
}
add_action( 'update_option_woocommerce_gateway_order', 'apg_free_shipping_borra_cache_metodos_pago' );
add_action( 'woocommerce_update_options_payment_gateways', 'apg_free_shipping_borra_cache_metodos_pago' );

//Limpia la caché de los zonas de envío
function apg_free_shipping_borra_cache_zonas_envio() {
	delete_transient( 'apg_free_shipping_zonas_de_envio' );
}
add_action( 'woocommerce_update_options_shipping', 'apg_free_shipping_borra_cache_zonas_envio' );

//Limpia la caché de métodos de envío al guardar opciones
function apg_free_shipping_borra_cache_metodos_envio( $instance_id ) {
	delete_transient( 'apg_free_shipping_metodos_envio_' . absint( $instance_id ) );
}
add_action( 'woocommerce_update_shipping_method', 'apg_free_shipping_borra_cache_metodos_envio' );

//Limpia la caché de atributos
function apg_free_shipping_borra_cache_atributos() {
	delete_transient( 'apg_free_shipping_atributos' );
}
add_action( 'woocommerce_attribute_added', 'apg_free_shipping_borra_cache_atributos', 10 );
add_action( 'woocommerce_attribute_updated', 'apg_free_shipping_borra_cache_atributos', 10 );
add_action( 'woocommerce_attribute_deleted', 'apg_free_shipping_borra_cache_atributos', 10 );