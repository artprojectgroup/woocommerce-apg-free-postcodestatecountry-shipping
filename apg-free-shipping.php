<?php
/*
Plugin Name: WooCommerce - APG Free Postcode/State/Country Shipping
Version: 0.9
Plugin URI: http://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province ( state ) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="http://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="http://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WooCommerce - APG Weight and Postcode/State/Country Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="http://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: http://www.artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.1

Text Domain: apg_free_shipping
Domain Path: /i18n/languages

@package WooCommerce - APG Free Postcode/State/Country Shipping
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

//Definimos constantes
define( 'DIRECCION_apg_free_shipping', plugin_basename( __FILE__ ) );

//Definimos las variables
$apg_free_shipping = array( 	
	'plugin' 		=> 'WooCommerce - APG Free Postcode/State/Country Shipping', 
	'plugin_uri' 	=> 'woocommerce-apg-free-postcodestatecountry-shipping', 
	'donacion' 		=> 'http://www.artprojectgroup.es/tienda/donacion',
	'plugin_url' 	=> 'http://www.artprojectgroup.es/plugins-para-wordpress/plugins-para-woocommerce/woocommerce-apg-free-postcodestatecountry-shipping', 
	'ajustes' 		=> 'admin.php?page=wc-settings&tab=shipping&section=apg_free_shipping', 
	'puntuacion' 	=> 'http://wordpress.org/support/view/plugin-reviews/woocommerce-apg-free-postcodestatecountry-shipping'
 );

//Carga el idioma
load_plugin_textdomain( 'apg_free_shipping', null, dirname( DIRECCION_apg_free_shipping ) . '/i18n/languages' );

//Enlaces adicionales personalizados
function apg_free_shipping_enlaces( $enlaces, $archivo ) {
	global $apg_free_shipping;

	if ( $archivo == DIRECCION_apg_free_shipping ) {
		$plugin = apg_free_shipping_plugin( $apg_free_shipping['plugin_uri'] );
		$enlaces[] = '<a href="' . $apg_free_shipping['donacion'] . '" target="_blank" title="' . __( 'Make a donation by ', 'apg_free_shipping' ) . 'APG"><span class="genericon genericon-cart"></span></a>';
		$enlaces[] = '<a href="'. $apg_free_shipping['plugin_url'] . '" target="_blank" title="' . $apg_free_shipping['plugin'] . '"><strong class="artprojectgroup">APG</strong></a>';
		$enlaces[] = '<a href="https://www.facebook.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'Google+" target="_blank"><span class="genericon genericon-googleplus-alt"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="' . __( 'Follow us on ', 'apg_free_shipping' ) . 'LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a>';
		$enlaces[] = '<a href="http://profiles.wordpress.org/artprojectgroup/" title="' . __( 'More plugins on ', 'apg_free_shipping' ) . 'WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<a href="mailto:info@artprojectgroup.es" title="' . __( 'Contact with us by ', 'apg_free_shipping' ) . 'e-mail"><span class="genericon genericon-mail"></span></a> <a href="skype:artprojectgroup" title="' . __( 'Contact with us by ', 'apg_free_shipping' ) . 'Skype"><span class="genericon genericon-wordpress"></span></a>';
		$enlaces[] = '<div class="star-holder rate"><div style="width:' . esc_attr( str_replace( ',', '.', $plugin['rating'] ) ) . 'px;" class="star-rating"></div><div class="star-rate"><a title="' . __( '***** Fantastic!', 'apg_free_shipping' ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=5#postform" target="_blank"><span></span></a> <a title="' . __( '**** Great', 'apg_free_shipping' ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=4#postform" target="_blank"><span></span></a> <a title="' . __( '*** Good', 'apg_free_shipping' ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=3#postform" target="_blank"><span></span></a> <a title="' . __( '** Works', 'apg_free_shipping' ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=2#postform" target="_blank"><span></span></a> <a title="' . __( '* Poor', 'apg_free_shipping' ) . '" href="' . $apg_free_shipping['puntuacion'] . '?rate=1#postform" target="_blank"><span></span></a></div></div>';
	}
	
	return $enlaces;
}
add_filter( 'plugin_row_meta', 'apg_free_shipping_enlaces', 10, 2 );

//Añade el botón de configuración
function apg_free_shipping_enlace_de_ajustes( $enlaces ) { 
	global $apg_free_shipping;

	$enlace_de_ajustes = '<a href="' . $apg_free_shipping['ajustes'] . '" title="' . __( 'Settings of ', 'apg_free_shipping' ) . $apg_free_shipping['plugin'] . '">' . __( 'Settings', 'apg_free_shipping' ) . '</a>'; 
	array_unshift( $enlaces, $enlace_de_ajustes ); 
	
	return $enlaces; 
}
$plugin = DIRECCION_apg_free_shipping; 
add_filter( "plugin_action_links_$plugin", 'apg_free_shipping_enlace_de_ajustes' );

//Contine la clase que crea los nuevos gastos de envío
function apg_free_shipping_inicio() {
	if ( !class_exists( 'WC_Shipping_Method' ) ) {
		return;
	}

	class apg_free_shipping extends WC_Shipping_Method {				
		public	$clases_de_envio	= array();

		function __construct() {
			$this->id 				= 'apg_free_shipping';
			$this->method_title		= __( "APG Free Shipping", 'apg_free_shipping' );
			$this->init();
		}

		//Inicializa los datos
        function init() {
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			$this->apg_free_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío

			$this->init_settings();
			$campos = array( 
				'enabled', 
				'title', 
				'postal_group_no', 
				'state_group_no', 
				'country_group_no', 
				'availability', 
				'countries', 
				'requires', 
				'grupos_excluidos', 
				'clases_excluidas', 
				'importe_minimo', 
				'muestra',
				'availability',
				'countries',
				'importe_minimo',
				'requires'
			);
			foreach ( $campos as $campo ) {
				$this->$campo = isset( $this->settings[$campo] ) ? $this->settings[$campo] : '';
			}
			$this->init_form_fields();
			
			for ( $contador = 1; $this->postal_group_no >= $contador; $contador++ ) {
				if ( isset( $this->settings['P' . $contador] ) ) {
					$this->procesa_codigo_postal( $this->settings['P' . $contador], 'P' . $contador );
				}
			}
			$this->pinta_grupos_codigos_postales();
			$this->pinta_grupos_estados();
        }
		
		//Procesa el código postal
		function procesa_codigo_postal( $codigo_postal, $id ) {
			if ( strstr( $codigo_postal, '-' ) ) {
				$codigos_postales = explode( ';', $codigo_postal );
				$numeros_codigo_postal = array();
				foreach ( $codigos_postales as $codigo_postal ) {
					if ( strstr( $codigo_postal, '-' ) ) {
						$partes_codigo_postal = explode( '-', $codigo_postal );
						if ( is_numeric( $partes_codigo_postal[0] ) && is_numeric( $partes_codigo_postal[1] ) && $partes_codigo_postal[1] > $partes_codigo_postal[0] ) {
							for ( $i = $partes_codigo_postal[0]; $i <= $partes_codigo_postal[1]; $i++ ) {
								if ( $i ) {
									if ( strlen( $i ) < 5 ) {
										$i = str_pad( $i, 5, "0", STR_PAD_LEFT );
									}
									$numeros_codigo_postal[] = $i;
								}
							}
						}
					} else {
						$numeros_codigo_postal[] = $codigo_postal;
					}
				}
				$this->settings[$id] = implode( ';', $numeros_codigo_postal );
			}
		}
		
		//Formulario de datos
		function init_form_fields() {
			global $woocommerce;
			
			if ( get_option( 'woocommerce_enable_coupons' ) == 'yes' && $this->importe_minimo ) {
				$requerido = 'cualquiera';
			} else if ( get_option( 'woocommerce_enable_coupons' ) == 'yes' ) {
				$requerido = 'cupon';
			} else if ( $this->importe_minimo ) {
				$requerido = 'importe_minimo';
			} else {
				$requerido = '';
			}

			$this->form_fields = array( 
				'enabled' => array( 
					'title' 						=> __( 'Enable/Disable', 'apg_free_shipping' ),
					'type' 						=> 'checkbox',
					'label' 						=> __( 'Enable Free Shipping', 'apg_free_shipping' ),
					'default' 					=> 'yes'
				 ),
				'title' => array( 
					'title' 						=> __( 'Method Title', 'apg_free_shipping' ),
					'type' 						=> 'text',
					'description' 				=> __( 'This controls the title which the user sees during checkout.', 'apg_free_shipping' ),
					'default'					=> __( 'APG Free Shipping', 'apg_free_shipping' ),
					'desc_tip'					=> true,
				 ),
				'availability' => array( 
					'title' 						=> __( 'Method availability', 'apg_free_shipping' ),
					'type' 						=> 'select',
					'default' 					=> 'all',
					'class'						=> 'availability',
					'options'					=> array( 
						'all' 					=> __( 'All allowed countries', 'apg_free_shipping' ),
						'specific' 				=> __( 'Specific Countries', 'apg_free_shipping' )
					 )
				 ),
				'countries' => array( 
					'title' 						=> __( 'Specific Countries', 'apg_free_shipping' ),
					'type' 						=> 'multiselect',
					'class'						=> 'chosen_select',
					'css'						=> 'width: 450px;',
					'default' 					=> '',
					'options'					=> WC()->countries->get_shipping_countries(),
				 ),
				'requires' => array( 
					'title' 						=> __( 'Free Shipping Requires...', 'apg_free_shipping' ),
					'type' 						=> 'select',
					'default' 					=> $requerido,
					'options'					=> array( 
						''						=> __( 'N/A', 'apg_free_shipping' ),
						'cupon'					=> __( 'A valid free shipping coupon', 'apg_free_shipping' ),
						'importe_minimo'		=> __( 'A minimum order amount ( defined below )', 'apg_free_shipping' ),
						'cualquiera'			=> __( 'A minimum order amount OR a coupon', 'apg_free_shipping' ),
						'ambos'					=> __( 'A minimum order amount AND a coupon', 'apg_free_shipping' ),
					 )
				 ),
				'importe_minimo' => array( 
							'title'				=> __( 'Minimum Order Amount', 'apg_free_shipping' ),
							'type'				=> 'price',
							'description' 		=> __( 'Users will need to spend this amount to get free shipping ( if enabled above ).', 'apg_free_shipping' ),
							'default' 			=> '0',
							'desc_tip'      	=> true,
							'placeholder'		=> wc_format_localized_price( 0 )
				 ),
				'postal_group_no' => array( 
					'title'						=> __( 'Number of postcode groups', 'apg_free_shipping' ),
					'type'						=> 'number',
					'desc_tip'					=> __( 'Number of groups of ZIP/Postcode sharing delivery rates. ( Hit "Save changes" button after you have changed this setting ).', 'apg_free_shipping' ),
					'default'					=> '0',
				 ),
				'state_group_no' => array( 
					'title'						=> __( 'Number of state groups', 'apg_free_shipping' ),
					'type'						=> 'number',
					'desc_tip'					=> __( 'Number of groups of states sharing delivery rates. ( Hit "Save changes" button after you have changed this setting ).', 'apg_free_shipping' ),
					'default'					=> '0',
				 ),
				'grupos_excluidos' 				=> array( 
					'title'						=> __( 'No shipping', 'apg_free_shipping' ),
					'type'						=> 'text',
					'desc_tip'					=> sprintf( __( "Group/s of ZIP/Postcode/State where %s doesn't accept free shippings. Example: <code>Postcode/state group code separated by comma ( , )</code>", 'apg_free_shipping' ), get_bloginfo( 'name' ) ),
					'default'					=> '',
					'description'				=> '<code>P2,S1</code>',
				 ),
			 );
			if ( WC()->shipping->get_shipping_classes() ) {
				$this->form_fields['clases_excluidas'] = array( 
					'title'		=> __( 'No shipping ( Shipping class ):', 'apg_free_shipping' ),
					'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept free shippings.", 'apg_free_shipping' ), get_bloginfo( 'name' ) ),
					'css'		=> 'width: 450px;',
					'default'	=> '',
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'options' 	=> array( 'todas' => __( 'All enabled shipping class', 'apg_free_shipping' ) ) + $this->clases_de_envio,
				);
			}
			$this->form_fields['muestra'] = array( 
					'title'		=> __( 'Show only APG Free Shipping', 'apg_free_shipping' ),
					'type'		=> 'checkbox',
					'label'		=> __( "Don't show others shipping cost.", 'apg_free_shipping' ),
					'default'	=> 'no',
			 );
		}

		//Función que lee y devuelve los tipos de clases de envío
		function apg_free_shipping_dame_clases_de_envio() {
			if ( WC()->shipping->get_shipping_classes() ) {
				foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
					$this->clases_de_envio[esc_attr( $clase_de_envio->slug )] = $clase_de_envio->name;
				}
			} else {
				$this->clases_de_envio[] = __( 'Select a class&hellip;', 'apg_free_shipping' );
			}
		}	

		//Muestra los campos para los grupos de códigos postales
		function pinta_grupos_codigos_postales() {
			global $woocommerce;

			$numero = $this->postal_group_no;

			for ( $contador = 1; $numero >= $contador; $contador++ ) {
				$this->form_fields['P' . $contador] =  array( 
					'title'		=> sprintf( __( 'Postcode Group %s ( P%s )', 'apg_free_shipping' ), $contador, $contador ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Add the postcodes for this group. Semi-colon ( ; ) separate multiple values. Wildcards ( * ) can be used. Example: <code>07*</code>. Ranges for numeric postcodes will be expanded into individual postcodes. Example: <code>12345-12350</code>.', 'apg_free_shipping' ),
					'css'		=> 'width: 450px;',
					'default'	=> '',
				 );
			}
		}

		//Muestra los campos para los grupos de estados ( provincias )
		function pinta_grupos_estados() {
			global $woocommerce;

			$numero = $this->state_group_no;

			$base_country = $woocommerce->countries->get_base_country();

			for ( $contador = 1; $numero >= $contador; $contador++ ) {
				$this->form_fields['S' . $contador] =  array( 
					'title'		=> sprintf( __( 'State Group %s ( S%s )', 'apg_free_shipping' ), $contador, $contador ),
					'type'		=> 'multiselect',
					'class'		=> 'chosen_select',
					'css'		=> 'width: 450px;',
					'desc_tip'	=> __( 'Select the states for this group.', 'apg_free_shipping' ),
					'default'	=> '',
					'options'	=> $woocommerce->countries->get_states( $base_country ),
				 );
			}
		}

		//Calcula el gasto de envío
		function calculate_shipping() {
			global $woocommerce;

			$tarifa = array( 
				'id'		=> $this->id,
				'label'	=> $this->title,
				'cost'	=> 0,
				'taxes'	=> false,
			 );

			$this->add_rate( $tarifa );
		}

		//Selecciona el/los grupo/s según la dirección de envío del cliente
		function dame_grupos( $paquete = array() ) {
			$codigo_postal 			= strtoupper( woocommerce_clean( $paquete['destination']['postcode'] ) );
			$codigos_postales 		= array( $codigo_postal );
			$tamano_codigo_postal 	= strlen( $paquete['destination']['postcode'] );

			for ( $i = 0; $i < $tamano_codigo_postal; $i++ )  {
				$codigo_postal = substr( $codigo_postal, 0, -1 );
				$codigos_postales[] = $codigo_postal . '*';
			}

			$grupos = array ( 'P' => 'postcode', 'S' => 'state' );
			foreach ( $grupos as $letra => $nombre ) {
				$contador = 1;

				while ( isset( $this->settings[$letra . $contador] ) && $this->settings[$letra . $contador] ) {
				    if ( $nombre == 'postcode' ) {
						$grupos = explode( ";", $this->settings[$letra . $contador] );
						foreach ( $codigos_postales as $codigo_postal ) {
							foreach ( $grupos as $grupo_tarifa ) {
								if ( $codigo_postal == $grupo_tarifa ) {
									$grupo = $letra . $contador;
								}
							}
						}
					} else {
						if ( isset( $paquete['destination'][$nombre] ) && in_array( $paquete['destination'][$nombre], $this->settings[$letra . $contador] ) ) {
							$grupo = $letra . $contador;
						}
					}
				    $contador++;
				}

    	        if ( isset( $grupo ) ) {
					return $grupo;
				}
			}

			$paises = '';
			if ( $this->availability == 'specific' ) {
				$paises = $this->countries;
			} else {
				if ( get_option( 'woocommerce_allowed_countries' ) == 'specific' ) {
					$paises = get_option( 'woocommerce_specific_allowed_countries' );
				}
			}

			if ( is_array( $paises ) ) {
				if ( in_array( $paquete['destination']['country'], $paises ) ) {
					return 'C1';
				}
			}
			
			return NULL;
        }

		//Pinta el formulario
		public function admin_options() {
			wp_enqueue_style( 'apg_free_shipping_hoja_de_estilo' ); //Carga la hoja de estilo
			include( 'includes/formulario.php' );
		}
		
		//Habilita el envío
		function is_available( $paquete ) {
			global $woocommerce;

			if ( $this->enabled == "no" ) {
				return false;
			}

			if ( $this->clases_excluidas ) {
				//Toma distintos datos de los productos
				foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
					$producto = $valores['data'];

					//Clase de producto
					if ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) ) {
						return false; //No atiende a las clases de envío excluidas
					}
				}
			}

			$grupo = $this->dame_grupos( $paquete );
			if ( $grupo ) {
				$grupos_excluidos = explode( ',', preg_replace( '/\s+/', '', $this->grupos_excluidos ) );
				foreach ( $grupos_excluidos as $grupo_excluido ) {
					if ( $grupo_excluido == $grupo ) {
						return false; //No atiende a los grupos excluidos
					}
				}
			} else {
				return false;
			}

			$habilitado = $tiene_cupon = $tiene_importe_minimo = false;

			if ( in_array( $this->requires, array( 'cupon', 'cualquiera', 'ambos' ) ) ) {
				if ( $woocommerce->cart->applied_coupons ) {
					foreach ( $woocommerce->cart->applied_coupons as $codigo ) {
						$cupon = new WC_Coupon( $codigo );
						if ( $cupon->is_valid() && $cupon->enable_free_shipping() ) {
							$tiene_cupon = true;
						}
					}
				}
			}

			if ( in_array( $this->requires, array( 'importe_minimo', 'cualquiera', 'ambos' ) ) && isset( $woocommerce->cart->cart_contents_total ) ) {
				if ( $woocommerce->cart->prices_include_tax ) {
					$total = $woocommerce->cart->cart_contents_total + array_sum( $woocommerce->cart->taxes );
				} else {
					$total = $woocommerce->cart->cart_contents_total;
				}
				if ( $total >= $this->importe_minimo ) { 
					$tiene_importe_minimo = true;
				}
			}

			switch ( $this->requires ) {
				case 'importe_minimo' :
					if ( $tiene_importe_minimo ) $habilitado = true;
				break;
				case 'cupon' :
					if ( $tiene_cupon ) $habilitado = true;
				break;
				case 'ambos' :
					if ( $tiene_importe_minimo && $tiene_cupon ) $habilitado = true;
				break;
				case 'cualquiera' :
					if ( $tiene_importe_minimo || $tiene_cupon ) $habilitado = true;
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
	$methods[] = 'apg_free_shipping';

	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_anade_gastos_de_envio' );

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
	
	return $plugin;
}

//Carga las hojas de estilo
function apg_free_shipping_muestra_mensaje() {
	wp_register_style( 'apg_free_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', __FILE__ ) ); //Carga la hoja de estilo
	wp_register_style( 'apg_free_shipping_fuentes', plugins_url( 'assets/fonts/stylesheet.css', __FILE__ ) ); //Carga la hoja de estilo global
	wp_enqueue_style( 'apg_free_shipping_fuentes' ); //Carga la hoja de estilo global
}
add_action( 'admin_init', 'apg_free_shipping_muestra_mensaje' );

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_free_shipping_desinstalar() {
	delete_option( 'woocommerce_apg_free_shipping_settings' );
	delete_transient( 'apg_free_shipping_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_free_shipping_desinstalar' );
?>
