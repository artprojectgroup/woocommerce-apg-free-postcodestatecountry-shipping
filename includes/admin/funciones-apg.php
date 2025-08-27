<?php
/**
 * Funciones administrativas, enlaces y utilidades para WC - APG Free Shipping.
 *
 * Define variables globales, enlaces personalizados en la administración,
 * carga de hojas de estilo, integración de scripts y utilidades de valoración y soporte.
 *
 * @package WC-APG-Free-Shipping
 * @subpackage Includes/Admin
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

// Definimos las variables.
$apg_free_shipping = [ 	
	'plugin' 		=> 'WC - APG Free Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-free-postcodestatecountry-shipping', 
	'donacion' 		=> 'https://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'https://artprojectgroup.es/tienda/soporte-tecnico',
	'plugin_url' 	=> 'https://artprojectgroup.es/plugins-para-woocommerce/wc-apg-free-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/woocommerce-apg-free-postcodestatecountry-shipping'
];
$medios_de_pago = get_transient( 'apg_shipping_metodos_de_pago' );
$zonas_de_envio = get_transient( 'apg_shipping_zonas_de_envio' );

/**
 * Añade enlaces personalizados en la fila del plugin en la lista de plugins.
 *
 * Incluye enlaces a donación, perfil del autor, redes sociales, otros plugins, contacto y valoración.
 *
 * @param array  $enlaces  Enlaces actuales.
 * @param string $archivo  Archivo del plugin procesado.
 * @return array Enlaces modificados.
 */
function apg_free_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_free_shipping;

	if ( $archivo == DIRECCION_apg_free_shipping ) {
		$enlaces[] = '<a href="' . $apg_free_shipping[ 'donacion' ] . '" target="_blank" title="' . __( 'Make a donation by ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_free_shipping[ 'plugin_url' ] . '" target="_blank" title="' . $apg_free_shipping[ 'plugin' ] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . 'Skype"><span class="genericon genericon-skype"></span></a>';
		$enlaces[] = apg_free_shipping_plugin( $apg_free_shipping[ 'plugin_uri' ] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_free_shipping_enlaces', 10, 2 );

/**
 * Añade enlaces de acceso rápido a la configuración y soporte del plugin en la página de plugins.
 *
 * @param array $enlaces Enlaces de acción actuales.
 * @return array Enlaces de acción con ajustes y soporte añadidos al principio.
 */
function apg_free_shipping_enlace_de_ajustes( $enlaces ) { 
	global $apg_free_shipping;

	$enlaces_de_ajustes = [
		'<a href="' . $apg_free_shipping[ 'ajustes' ] . '" title="' . __( 'Settings of ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . $apg_free_shipping[ 'plugin' ] .'">' . __( 'Settings', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</a>', 
		'<a href="' . $apg_free_shipping[ 'soporte' ] . '" title="' . __( 'Support of ', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . $apg_free_shipping[ 'plugin' ] .'">' . __( 'Support', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</a>'
	];
	foreach( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_free_shipping; 
add_filter( "plugin_action_links_$plugin", 'apg_free_shipping_enlace_de_ajustes' );

/**
 * Muestra un aviso especial en la pantalla de actualización cuando hay cambios mayores en el plugin.
 *
 * @param array    $datos_version_actual Datos de la versión instalada.
 * @param stdClass $datos_nueva_version  Datos de la nueva versión del repositorio.
 * @return void
 */
function apg_free_shipping_notificacion( $datos_version_actual, $datos_nueva_version ) {
    if ( isset( $datos_nueva_version->upgrade_notice ) && strlen( trim( $datos_nueva_version->upgrade_notice ) ) > 0 && version_compare( $datos_version_actual['Version'], '2.0', '<' ) ) {
        $mensaje = '</p><div class="wc_plugin_upgrade_notice">';
		$mensaje .= __( "<h4>ALERT: 2.0 is a major update</h4>It’s important that you make backups of your <strong>WC - APG Free Shipping</strong> current configuration and configure it again after upgrade.<br /><em>Remember, the current setting is totally incompatible with WooCommerce 2.6 and you'll lose it</em>.", 'woocommerce-apg-free-postcodestatecountry-shipping' );
        $mensaje .= '</div><p>';
		
		echo wp_kses_post( $mensaje );
	}
}
add_action( 'in_plugin_update_message-woocommerce-apg-free-postcodestatecountry-shipping/apg-free-shipping.php', 'apg_free_shipping_notificacion', 10, 2 );

/**
 * Obtiene la información pública del plugin desde la API de WordPress.org y muestra la valoración.
 *
 * Cachea la respuesta durante 24 horas.
 *
 * @param string $nombre Slug del plugin.
 * @return string HTML con las estrellas de valoración o un mensaje de error.
 */
function apg_free_shipping_plugin( $nombre ) {
	global $apg_free_shipping;
	
	$respuesta	= get_transient( 'apg_free_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=' . $nombre  );
		set_transient( 'apg_free_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( ! is_wp_error( $respuesta ) ) {
		$plugin = json_decode( wp_remote_retrieve_body( $respuesta ) );
	} else {
        // translators: %s is the plugin name.
	   return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $apg_free_shipping[ 'plugin' ] ) . '" href="' . $apg_free_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . __( 'Unknown rating', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</a>';
	}

    $rating = [
	   'rating'		=> $plugin->rating,
	   'type'		=> 'percent',
	   'number'		=> $plugin->num_ratings,
	];
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

    // translators: %s is the plugin name.
	return '<a title="' . sprintf( __( 'Please, rate %s:', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $apg_free_shipping[ 'plugin' ] ) . '" href="' . $apg_free_shipping[ 'puntuacion' ] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

/**
 * Encola la hoja de estilo y el JavaScript necesarios en la administración del plugin.
 *
 * Solo carga los recursos en las páginas relevantes de ajustes de WooCommerce o plugins.
 *
 * @return void
 */
function apg_free_shipping_estilo() {
    $request_uri    = isset( $_SERVER[ 'REQUEST_URI' ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ) : '';
    if ( strpos( $request_uri, 'wc-settings&tab=shipping&instance_id' ) !== false || strpos( $request_uri, 'plugins.php' ) !== false ) {
		wp_enqueue_style( 'apg_free_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', DIRECCION_apg_free_shipping ), [], VERSION_apg_free_shipping ); // Carga la hoja de estilo global.
	}
    if ( strpos( $request_uri, 'wc-settings&tab=shipping' ) !== false ) {
		wp_enqueue_script( 'apg_free_shipping_script', plugins_url( 'assets/js/apg-free-shipping.js', DIRECCION_apg_free_shipping ), [ 'jquery' ], VERSION_apg_free_shipping, true );
	}
}
add_action( 'admin_enqueue_scripts', 'apg_free_shipping_estilo' );
