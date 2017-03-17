<?php
/*
Plugin Name: WooCommerce - APG Free Postcode/State/Country Shipping
Version: 2.1.0.1
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province (state) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="http://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="http://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WooCommerce - APG Weight and Postcode/State/Country Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="http://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: http://artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.7.3

Text Domain: apg_free_shipping
Domain Path: /languages

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
	'donacion' 		=> 'http://artprojectgroup.es/tienda/donacion',
	'soporte' 		=> 'http://wcprojectgroup.es/tienda/ticket-de-soporte',
	'plugin_url' 	=> 'http://artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/woocommerce-apg-free-postcodestatecountry-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping', 
	'puntuacion' 	=> 'https://wordpress.org/support/view/plugin-reviews/woocommerce-apg-free-postcodestatecountry-shipping'
);
$envios_adicionales_free = $limpieza_free = NULL;

//Carga el idioma
load_plugin_textdomain( 'apg_free_shipping', null, dirname( DIRECCION_apg_free_shipping ) . '/languages' );

//Enlaces adicionales personalizados
function apg_free_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_free_shipping;

	if ( $archivo == DIRECCION_apg_free_shipping ) {
		$enlaces[] = '<a href="' . $apg_free_shipping['donacion'] . '" target="_blank" title="' . __( 'Make a donation by ', 'apg_free_shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_free_shipping['plugin_url'] . '" target="_blank" title="' . $apg_free_shipping['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="https://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'apg_free_shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
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

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//¿Está activo WooCommerce?
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
	//Contine la clase que crea los nuevos gastos de envío
	function apg_free_shipping_inicio() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}

		class WC_apg_free_shipping extends WC_Shipping_Method {				
			public $clases_de_envio		= array();
			public $roles_de_usuario	= array();

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
				$this->apg_free_shipping_dame_roles_de_usuario(); //Obtiene todos los roles de usuario
	
				$this->init_form_fields();
				$this->init_settings();

				//Inicializamos variables
				$campos = array( 
					'activo', 
					'title', 
					'requires', 
					'importe_minimo', 
					'clases_excluidas', 
					'roles_excluidos', 
					'icono',
					'muestra_icono',
					'entrega',
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
				$this->instance_form_fields = include( 'includes/admin/campos.php' );
			}
			
			//Pinta el formulario
			public function admin_options() {
				include_once( 'includes/formulario.php' );
			}

			//Fuerza a mostrar el formulario
			public function get_instance_form_fields() {
				if ( is_admin() ) {
					wc_enqueue_js( "
						jQuery( function( $ ) {
							$( 'a.wc-shipping-zone-method-settings' ).removeClass( 'wc-shipping-zone-method-settings' );
						});
					" );
				}

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
			
			//Función que lee y devuelve los roles de usuario
			public function apg_free_shipping_dame_roles_de_usuario() {
				$wp_roles= new WP_Roles();

				foreach( $wp_roles->role_names as $role => $nombre ) {
					$this->roles_de_usuario[$role] = $nombre;
				}				
			}	
	
			//Calcula el gasto de envío
			public function calculate_shipping( $paquete = array() ) {
				$this->add_rate( array(
					'id'		=> $this->get_rate_id(),
					'label'		=> $this->title,
					'cost'		=> 0,
					'taxes'		=> false
				) );
			}

			//Habilita el envío
			public function is_available( $paquete ) {
				if ( $this->activo == 'no' ) {
					return false; //No está activo
				}
				
				//Comprobamos los roles excluidos
				if ( !empty( $this->roles_excluidos ) ) {
					if ( empty( wp_get_current_user()->roles ) && in_array( 'invitado', $this->roles_excluidos ) ) { //Usuario invitado
						return false; //Role excluido
					}
					foreach( wp_get_current_user()->roles as $rol ) { //Usuario con rol
						if ( in_array( $rol, $this->roles_excluidos ) ) {
							return false; //Role excluido
						}
					}
				}

				//Variable
				$total_clases_excluidas = 0;
				
				//Comprobamos las clases excluidas
				if ( $this->clases_excluidas ) {
					//Toma distintos datos de los productos
					foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
						$producto = $valores['data'];
	
						//Clase de producto
						if ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || in_array( 'todas', $this->clases_excluidas ) ) {
							$total_clases_excluidas = ( WC()->cart->tax_display_cart == 'excl' ) ? $total_clases_excluidas + $producto->get_price_excluding_tax() * $valores['quantity'] : $total_clases_excluidas + $producto->get_price_including_tax() * $valores['quantity'];
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

					$total = ( 'incl' === WC()->cart->tax_display_cart ) ? $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() ) : $total - WC()->cart->get_cart_discount_total();
		
					if ( $total - $total_clases_excluidas >= $this->importe_minimo ) {
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

//Muestra el icono
function apg_free_shipping_icono( $etiqueta, $metodo ) {
	$id				= explode( ":", $metodo->id );
	$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $id[1] .'_settings' ) );
	//¿Mostramos el icono?
	if ( !empty( $configuracion['icono'] ) && @getimagesize( $configuracion['icono'] ) && $configuracion['muestra_icono'] != 'no' ) {
		$tamano = @getimagesize( $configuracion['icono'] );
		$imagen	= '<img class="apg_free_shipping_icon" src="' . $configuracion['icono'] . '" witdh="' . $tamano[0] . '" height="' . $tamano[1] . '" />';
		if ( $configuracion['muestra_icono'] == 'delante' ) {
			$etiqueta = $imagen . ' ' . $etiqueta;
		} else if ( $configuracion['muestra_icono'] == 'detras' ) {
			$etiqueta = $etiqueta . ' ' . $imagen;
		} else {
			$etiqueta = $imagen;
		}
	}
	//Tiempo de entrega
	if ( !empty( $configuracion['entrega'] ) ) {
		$etiqueta .= '<br /><small class="apg_shipping_delivery">' . sprintf( __( "Estimated delivery time: %s", 'apg_free_shipping' ), $configuracion['entrega'] ) . '</small>';
	}

	return $etiqueta;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'apg_free_shipping_icono', PHP_INT_MAX, 2 );

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_free_shipping_requiere_wc() {
	global $apg_free_shipping;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_free_shipping['plugin'] . '</h3><h4>' . __( "This plugin require WooCommerce active to run!", 'apg_free_shipping' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_free_shipping );
}

//Oculta el resto de gastos de envío
function apg_free_shipping_oculta_envios( $envios ) {
	$envio_gratis = array();
	foreach ( $envios as $clave => $envio ) {
		if ( 'apg_free_shipping' === $envio->method_id ) {
			$envio_gratis[ $clave ] = $envio;
		}
	}
 
	return !empty( $envio_gratis ) ? $envio_gratis : $envios;
}

//Obtiene toda la información sobre el plugin
function apg_free_shipping_plugin( $nombre ) {
	global $apg_free_shipping;

	$argumentos = ( object ) array( 
		'slug'		=> $nombre 
	);
	$consulta = array( 
		'action'		=> 'plugin_information', 
		'timeout'	=> 15, 
		'request'	=> serialize( $argumentos )
	);
	$respuesta = get_transient( 'apg_free_shipping_plugin' );
	if ( false === $respuesta ) {
		$respuesta = wp_remote_post( 'http://api.wordpress.org/plugins/info/1.0/', array( 
			'body'	=> $consulta
		) );
		set_transient( 'apg_free_shipping_plugin', $respuesta, 24 * HOUR_IN_SECONDS );
	}
	if ( !is_wp_error( $respuesta ) ) {
		$plugin = get_object_vars( unserialize( $respuesta['body'] ) );
	} else {
		$plugin['rating'] = 100;
	}
	
	$rating = array(
	   'rating'		=> $plugin['rating'],
	   'type'		=> 'percent',
	   'number'		=> $plugin['num_ratings'],
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
