<?php
/*
Plugin Name: WooCommerce - APG Free Postcode/State/Country Shipping
Version: 2.0
Plugin URI: http://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province (state) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="http://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="http://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WooCommerce - APG Weight and Postcode/State/Country Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="http://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: http://www.artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.6

Text Domain: apg_free_shipping
Domain Path: /i18n/languages

@package WooCommerce - APG Free Postcode/State/Country Shipping
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

//Definimos constantes
define( 'DIRECCION_apg_free_shipping', plugin_basename( __FILE__ ) );

//Definimos las variables
$apg_free_shipping = array( 	
	'plugin' 		=> 'WooCommerce - APG Free Postcode/State/Country Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-free-postcodestatecountry-shipping', 
	'donacion' 		=> 'http://www.artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'http://www.wcprojectgroup.es/tienda/ticket-de-soporte',
	'plugin_url' 	=> 'http://www.artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/woocommerce-apg-free-postcodestatecountry-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping&section=apg_free_shipping', 
	'puntuacion' 	=> 'http://wordpress.org/support/view/plugin-reviews/woocommerce-apg-free-postcodestatecountry-shipping'
);
$envios_adicionales_free = $limpieza_free = NULL;

//Carga el idioma
load_plugin_textdomain( 'apg_free_shipping', null, dirname( DIRECCION_apg_free_shipping ) . '/i18n/languages' );

//Enlaces adicionales personalizados
function apg_free_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_free_shipping;

	if ( $archivo == DIRECCION_apg_free_shipping ) {
		$enlaces[] = '<a href="' . $apg_free_shipping['donacion'] . '" target="_blank" title="' . __( 'Make a donation by ', 'apg_free_shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_free_shipping['plugin_url'] . '" target="_blank" title="' . $apg_free_shipping['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="http://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'apg_free_shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'apg_free_shipping' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'apg_free_shipping' ) . 'Skype"><span class="genericon genericon-skype"></span></a>';
		$enlaces[] = apg_free_shipping_plugin( $apg_free_shipping['plugin_uri'] );
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_free_shipping_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_free_shipping_enlace_de_ajustes( $enlaces ) { 
	global $apg_free_shipping;

	$enlaces_de_ajustes = array(
		'<a href="' . $apg_free_shipping['ajustes'] . '" title="' . __( 'Settings of ', 'apg_free_shipping' ) . $apg_free_shipping['plugin'] .'">' . __( 'Settings', 'apg_free_shipping' ) . '</a>', 
		'<a href="' . $apg_free_shipping['soporte'] . '" title="' . __( 'Support of ', 'apg_free_shipping' ) . $apg_free_shipping['plugin'] .'">' . __( 'Support', 'apg_free_shipping' ) . '</a>'
	);
	foreach( $enlaces_de_ajustes as $enlace_de_ajustes ) {
		array_unshift( $enlaces, $enlace_de_ajustes );
	}
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_free_shipping; 
add_filter( "plugin_action_links_$plugin", 'apg_free_shipping_enlace_de_ajustes' );

//Añade notificación de actualización
function apg_free_shipping_noficacion( $datos_version_actual, $datos_nueva_version ) {
	if ( isset( $datos_nueva_version->upgrade_notice ) && strlen( trim( $datos_nueva_version->upgrade_notice ) ) > 0 && (float) $datos_version_actual['Version'] < 2.0 ){
        $mensaje = '</p><div class="wc_plugin_upgrade_notice">';
		$mensaje .= __( "<h4>ALERT: 2.0 is a major update</h4>It’s important that you make backups of your <strong>WooCommerce - APG Free Postcode/State/Country Shipping</strong> current configuration and configure it again after upgrade.<br /><em>Remember, the current setting is totally incompatible with WooCommerce 2.6 and you'll lose it</em>.", "apg_free_shipping" );
        $mensaje .= '</div><p>';
		
		echo $mensaje;
	}
}
add_action( 'in_plugin_update_message-woocommerce-apg-free-postcodestatecountry-shipping/apg-free-shipping.php', 'apg_free_shipping_noficacion', 10, 2 );

//¿Está activo WooCommerce?
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	//Contine la clase que crea los nuevos gastos de envío
	function apg_free_shipping_inicio() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}

		class WC_apg_free_shipping extends WC_Shipping_Method {				
			public $clases_de_envio	= array();
			public $importe_minimo	= 0;

			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_free_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( "APG Free Shipping", 'apg_free_shipping' );
				$this->method_description	= __( 'Lets you add a free shipping based on Postcode/State/Country of the cart and minimum order a amount and/or a valid free shipping coupon.', 'apg_free_shipping' );
				$this->supports				= array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);
				$this->init();
			}

			//Inicializa los datos
	        public function init() {
				$this->apg_free_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío
	
				//Inicializamos el campo requires
				if ( get_option( 'woocommerce_enable_coupons' ) == 'yes' && $this->importe_minimo ) {
					$this->requerido = 'cualquiera';
				} else if ( get_option( 'woocommerce_enable_coupons' ) == 'yes' ) {
					$this->requerido = 'cupon';
				} else if ( $this->importe_minimo ) {
					$this->requerido = 'importe_minimo';
				} else {
					$this->requerido = '';
				}

				$this->init_form_fields();
				$this->init_settings();
				$campos = array( 
					'title', 
					'requires', 
					'importe_minimo', 
					'clases_excluidas', 
					'muestra',
				);
				foreach ( $campos as $campo ) {
					$this->$campo = $this->get_option( $campo );
				}

				//Acción
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}
			
			//Formulario de datos
			public function init_form_fields() {

				$this->instance_form_fields = array( 
					'title' => array( 
						'title' 						=> __( 'Method Title', 'apg_free_shipping' ),
						'type' 						=> 'text',
						'description' 				=> __( 'This controls the title which the user sees during checkout.', 'apg_free_shipping' ),
						'default'					=> $this->method_title,
						'desc_tip'					=> true,
					 ),
					'requires' => array( 
						'title' 						=> __( 'Free Shipping Requires...', 'apg_free_shipping' ),
						'type' 						=> 'select',
						'default' 					=> $this->requerido,
						'class'						=> 'wc-enhanced-select',
						'options'					=> array( 
							''						=> __( 'N/A', 'apg_free_shipping' ),
							'cupon'					=> __( 'A valid free shipping coupon', 'apg_free_shipping' ),
							'importe_minimo'		=> __( 'A minimum order amount (defined below)', 'apg_free_shipping' ),
							'cualquiera'			=> __( 'A minimum order amount OR a coupon', 'apg_free_shipping' ),
							'ambos'					=> __( 'A minimum order amount AND a coupon', 'apg_free_shipping' ),
						 )
					 ),
					'importe_minimo' => array( 
								'title'				=> __( 'Minimum Order Amount', 'apg_free_shipping' ),
								'type'				=> 'price',
								'description' 		=> __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'apg_free_shipping' ),
								'default' 			=> '0',
								'desc_tip'      	=> true,
								'placeholder'		=> wc_format_localized_price( 0 )
					 ),
				 );
				if ( WC()->shipping->get_shipping_classes() ) {
					$this->instance_form_fields['clases_excluidas'] = array( 
						'title'		=> __( 'No shipping (Shipping class)', 'apg_free_shipping' ),
						'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept free shippings.", 'apg_free_shipping' ), get_bloginfo( 'name' ) ),
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'type'		=> 'multiselect',
						'class'		=> 'wc-enhanced-select',
						'options' 	=> array( 'todas' => __( 'All enabled shipping class', 'apg_free_shipping' ) ) + $this->clases_de_envio,
					);
				}
				$this->instance_form_fields['muestra'] = array( 
						'title'		=> __( 'Show only APG Free Shipping', 'apg_free_shipping' ),
						'type'		=> 'checkbox',
						'label'		=> __( "Don't show others shipping cost.", 'apg_free_shipping' ),
						'default'	=> 'no',
				 );
			}
			
			//Pinta el formulario
			public function admin_options() {
				include( 'includes/formulario.php' );
			}

			public function get_instance_form_fields() {
				return parent::get_instance_form_fields();
			}
	
			//Función que lee y devuelve los tipos de clases de envío
			public function apg_free_shipping_dame_clases_de_envio() {
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
						$this->clases_de_envio[esc_attr( $clase_de_envio->slug )] = $clase_de_envio->name;
					}
				} else {
					$this->clases_de_envio[] = __( 'Select a class&hellip;', 'apg_free_shipping' );
				}
			}	
	
			//Calcula el gasto de envío
			public function calculate_shipping( $package = array() ) {
				$this->add_rate( array( 
					'id'	=> $this->id,
					'label'	=> $this->title,
					'cost'	=> 0,
					'taxes'	=> false,
				 ) );
			}

			//Habilita el envío
			public function is_available( $paquete ) {
				if ( $this->clases_excluidas ) {
					//Toma distintos datos de los productos
					foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
						$producto = $valores['data'];
	
						//Clase de producto
						if ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || in_array( 'todas', $this->clases_excluidas ) ) {
							return false; //No atiende a las clases de envío excluidas
						}
					}
				}
	
				//Variables
				$habilitado				= false;
				$tiene_cupon			= false;
				$tiene_importe_minimo	= false;
	
				if ( in_array( $this->requires, array( 'cupon', 'cualquiera', 'ambos' ) ) ) {
					if ( $cupones = WC()->cart->get_coupons() ) {
						foreach ( $cupones as $codigo => $cupon ) {
							if ( $cupon->is_valid() && $cupon->enable_free_shipping() ) {
								$tiene_cupon = true;
								break;
							}
						}
					}
				}
	
				if ( in_array( $this->requires, array( 'importe_minimo', 'cualquiera', 'ambos' ) ) && isset( WC()->cart->cart_contents_total ) ) {
					$total = WC()->cart->get_displayed_subtotal();

					if ( 'incl' === WC()->cart->tax_display_cart ) {
						$total = $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() );
					} else {
						$total = $total - WC()->cart->get_cart_discount_total();
					}
		
					if ( $total >= $this->importe_minimo ) {
						$tiene_importe_minimo = true;
					}
				}
	
				switch ( $this->requires ) {
					case 'importe_minimo' :
						if ( $tiene_importe_minimo ) {
							$habilitado = true;
						}
					break;
					case 'cupon' :
						if ( $tiene_cupon ) {
							$habilitado = true;
						}
					break;
					case 'ambos' :
						if ( $tiene_importe_minimo && $tiene_cupon ) {
							$habilitado = true;
						}
					break;
					case 'cualquiera' :
						if ( $tiene_importe_minimo || $tiene_cupon ) {
							$habilitado = true;
						}
					break;
					default :
						$habilitado = true;
					break;
				}
	
				if ( $this->muestra == 'yes' ) {
					add_filter( 'woocommerce_package_rates', 'apg_free_shipping_oculta_envios' , 10, 1 );
				}
				
				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $habilitado );
			}
		}
	}
	add_action( 'plugins_loaded', 'apg_free_shipping_inicio', 0 );
	
	//Añade APG Shipping a WooCommerce
	function apg_free_shipping_anade_gastos_de_envio( $methods ) {
		$methods[ 'apg_free_shipping' ] = 'WC_apg_free_shipping';
	
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_anade_gastos_de_envio' );
} else {
	add_action( 'admin_notices', 'apg_free_shipping_requiere_wc' );
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_free_shipping_requiere_wc() {
	global $apg_free_shipping;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_free_shipping['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'apg_free_shipping' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_free_shipping );
}

//Oculta el resto de gastos de envío
function apg_free_shipping_oculta_envios( $envios ) {
	if ( isset( $envios['apg_free_shipping'] ) ) {
		foreach ( $envios as $envio ) {
			if ( $envio->id != 'apg_free_shipping' ) {
				unset( $envios[$envio->id] );
			}
		}
	}
 
	return $envios;
}

//Obtiene toda la información sobre el plugin
function apg_free_shipping_plugin( $nombre ) {
	global $apg_free_shipping;
	
	$argumentos = ( object ) array( 
		'slug' => $nombre 
	);
	$consulta = array( 
		'action' => 'plugin_information', 
		'timeout' => 15, 
		'request' => serialize( $argumentos )
	);
	$respuesta = get_transient( 'apg_free_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'http://api.wordpress.org/plugins/info/1.0/', array( 
			'body' => $consulta)
		);
		set_transient( 'apg_free_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}

	$rating = array(
	   'rating'	=> $plugin['rating'],
	   'type'	=> 'percent',
	   'number'	=> $plugin['num_ratings'],
	);
	ob_start();
	wp_star_rating( $rating );
	$estrellas = ob_get_contents();
	ob_end_clean();

	return '<a title="' . sprintf( __( 'Please, rate %s:', 'apg_free_shipping' ), $apg_free_shipping['plugin'] ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=5#postform" class="estrellas">' . $estrellas . '</a>';
}

//Hoja de estilo
function apg_free_shipping_muestra_mensaje() {
	wp_register_style( 'apg_free_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', __FILE__ ) );
	wp_enqueue_style( 'apg_free_shipping_hoja_de_estilo' ); //Carga la hoja de estilo		
}
add_action( 'admin_init', 'apg_free_shipping_muestra_mensaje' );

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_free_shipping_desinstalar() {
	$contador = 0;
	while( $contador < 100 ) {
		delete_option( 'woocommerce_apg_free_shipping_' . $contador . 'settings' );
		$contador++;
	}
	delete_transient( 'apg_free_shipping_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_free_shipping_desinstalar' );
?>
