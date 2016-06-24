<?php
/*
Plugin Name: WooCommerce - APG Free Postcode/State/Country Shipping
Version: 1.1.1
Plugin URI: http://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province (state) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="http://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="http://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WooCommerce - APG Weight and Postcode/State/Country Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="http://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: http://www.artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 4.5.3

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

//¿Está activo WooCommerce?
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	//Contine la clase que crea los nuevos gastos de envío
	function apg_free_shipping_inicio() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}

		class apg_free_shipping extends WC_Shipping_Method {				
			public	$clases_de_envio	= array();

			public function __construct() {
				$this->id 				= 'apg_free_shipping';
				$this->method_title		= __( "APG Free Shipping", 'apg_free_shipping' );
				$this->init();
			}

			//Inicializa los datos
	        public function init() {
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
			public function procesa_codigo_postal( $codigo_postal, $id ) {
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
			public function init_form_fields() {
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
						'class'						=> 'wc-enhanced-select availability',
						'options'					=> array( 
							'all' 					=> __( 'All allowed countries', 'apg_free_shipping' ),
							'specific' 				=> __( 'Specific Countries', 'apg_free_shipping' )
						 )
					 ),
					'countries' => array( 
						'title' 						=> __( 'Specific Countries', 'apg_free_shipping' ),
						'type' 						=> 'multiselect',
						'class'						=> 'wc-enhanced-select',
						'css'						=> 'width: 450px;',
						'default' 					=> '',
						'options'					=> WC()->countries->get_shipping_countries(),
					 ),
					'requires' => array( 
						'title' 						=> __( 'Free Shipping Requires...', 'apg_free_shipping' ),
						'type' 						=> 'select',
						'default' 					=> $requerido,
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
					'postal_group_no' => array( 
						'title'						=> __( 'Number of postcode groups', 'apg_free_shipping' ),
						'type'						=> 'number',
						'desc_tip'					=> __( 'Number of groups of ZIP/Postcode sharing delivery rates.  Hit "Save changes" button after you have changed this setting).', 'apg_free_shipping' ),
						'default'					=> '0',
					 ),
					'state_group_no' => array( 
						'title'						=> __( 'Number of state groups', 'apg_free_shipping' ),
						'type'						=> 'number',
						'desc_tip'					=> __( 'Number of groups of states sharing delivery rates. (Hit "Save changes" button after you have changed this setting).', 'apg_free_shipping' ),
						'default'					=> '0',
					 ),
					'grupos_excluidos' 				=> array( 
						'title'						=> __( 'No shipping', 'apg_free_shipping' ),
						'type'						=> 'text',
						'desc_tip'					=> sprintf( __( "Group/s of ZIP/Postcode/State where %s doesn't accept free shippings. Example: <code>Postcode/state group code separated by comma (,)</code>", 'apg_free_shipping' ), get_bloginfo( 'name' ) ),
						'default'					=> '',
						'description'				=> '<code>P2,S1</code>',
					 ),
				 );
				if ( WC()->shipping->get_shipping_classes() ) {
					$this->form_fields['clases_excluidas'] = array( 
						'title'		=> __( 'No shipping (Shipping class)', 'apg_free_shipping' ),
						'desc_tip' 	=> sprintf( __( "Select the shipping class where %s doesn't accept free shippings.", 'apg_free_shipping' ), get_bloginfo( 'name' ) ),
						'css'		=> 'width: 450px;',
						'default'	=> '',
						'type'		=> 'multiselect',
						'class'		=> 'wc-enhanced-select',
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
			public function apg_free_shipping_dame_clases_de_envio() {
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
						$this->clases_de_envio[esc_attr( $clase_de_envio->slug )] = $clase_de_envio->name;
					}
				} else {
					$this->clases_de_envio[] = __( 'Select a class&hellip;', 'apg_free_shipping' );
				}
			}	
	
			//Muestra los campos para los grupos de códigos postales
			public function pinta_grupos_codigos_postales() {
				$numero = $this->postal_group_no;
	
				for ( $contador = 1; $numero >= $contador; $contador++ ) {
					$this->form_fields['P' . $contador] =  array( 
						'title'		=> sprintf( __( 'Postcode Group %s (P%s)', 'apg_free_shipping' ), $contador, $contador ),
						'type'		=> 'text',
						'desc_tip'	=> __( 'Add the postcodes for this group. Semi-colon (;) separate multiple values. Wildcards (*) can be used. Example: <code>07*</code>. Ranges for numeric postcodes will be expanded into individual postcodes. Example: <code>12345-12350</code>.', 'apg_free_shipping' ),
						'css'		=> 'width: 450px;',
						'default'	=> '',
					 );
				}
			}
	
			//Muestra los campos para los grupos de estados ( provincias )
			public function pinta_grupos_estados() {
				$numero = $this->state_group_no;
	
				$base_country = WC()->countries->get_base_country();
	
				for ( $contador = 1; $numero >= $contador; $contador++ ) {
					$this->form_fields['S' . $contador] =  array( 
						'title'		=> sprintf( __( 'State Group %s (S%s)', 'apg_free_shipping' ), $contador, $contador ),
						'type'		=> 'multiselect',
						'class'		=> 'wc-enhanced-select',
						'css'		=> 'width: 450px;',
						'desc_tip'	=> __( 'Select the states for this group.', 'apg_free_shipping' ),
						'default'	=> '',
						'options'	=> WC()->countries->get_states( $base_country ),
					 );
				}
			}
	
			//Calcula el gasto de envío
			public function calculate_shipping( $package = array() ) {
				$tarifa = array( 
					'id'	=> $this->id,
					'label'	=> $this->title,
					'cost'	=> 0,
					'taxes'	=> false,
				 );
	
				$this->add_rate( $tarifa );
			}
	
			//Selecciona el/los grupo/s según la dirección de envío del cliente
			public function dame_grupos( $paquete = array() ) {
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
				} else {
					return 'Vacío'; //No hay países seleccionados
				}
	
				return NULL;
			}
	
			//Pinta el formulario
			public function admin_options() {
				include( 'includes/formulario.php' );
			}
			
			//Habilita el envío
			public function is_available( $paquete ) {
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
				
				//Variables
				$habilitado				= false;
				$tiene_cupon			= false;
				$tiene_importe_minimo	= false;
	
				if ( in_array( $this->requires, array( 'cupon', 'cualquiera', 'ambos' ) ) ) {
					if ( WC()->cart->applied_coupons ) {
						foreach ( WC()->cart->applied_coupons as $codigo ) {
							$cupon = new WC_Coupon( $codigo );
							if ( $cupon->is_valid() && $cupon->enable_free_shipping() ) {
								$tiene_cupon = true;
							}
						}
					}
				}
	
				if ( in_array( $this->requires, array( 'importe_minimo', 'cualquiera', 'ambos' ) ) && isset( WC()->cart->cart_contents_total ) ) {
					if ( WC()->cart->prices_include_tax ) {
						$total = WC()->cart->cart_contents_total + array_sum( WC()->cart->taxes );
					} else {
						$total = WC()->cart->cart_contents_total;
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
	/*
	function apg_free_shipping_anade_gastos_de_envio( $methods ) {
		$methods[] = 'apg_free_shipping';
	
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_anade_gastos_de_envio' );
	*/
	
	//Función que lee y devuelve los nuevos gastos de envío
	function apg_free_shipping_lee_envios() {
		global $woocommerce, $envios_adicionales_free;
	
		if ( !is_array( $envios_adicionales_free ) || isset( $_POST['subtab'] ) ) {
			$envios_adicionales_free = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_apg_free_shipping' ) ) ) );
		}

		return $envios_adicionales_free;
	}
	
	//Función que convierte guiones en guiones bajos
	function apg_free_shipping_limpia_guiones( $texto ) {
		return str_replace( '-', '_', sanitize_title( $texto ) );
	}
	
	//Añade clases necesarias para nuevos gastos de envío
	function apg_free_shipping_clases( $metodos ) {
		foreach ( apg_free_shipping_lee_envios() as $clave => $envio ) {
			$limpio = apg_free_shipping_limpia_guiones( $envio );
			if ( !class_exists( "apg_free_shipping_$limpio") ) {
				eval("
			class apg_free_shipping_$limpio extends apg_free_shipping {
				public function __construct() {
					\$shipping = apg_free_shipping_lee_envios();
		
					\$this->id 				= \"apg_free_shipping_$limpio\";
					\$this->method_title	= __( \$shipping[$clave], 'apg_free_shipping' );
					add_action( 'woocommerce_update_options_shipping_' . \$this->id, array( \$this, 'process_admin_options' ) );
	
					parent::init();
				}
			}
				");
			}
		}	
	
		return $metodos;
	}
	add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_clases', 0 );
	
	//Añade APG Shipping a WooCommerce
	function apg_free_shipping_anade_gastos_de_envio( $metodos ) {
		global $limpieza_free;
		
		//Creamos los medios de envío
		$metodos[] = 'apg_free_shipping';
		$envios_apg = (array) apg_free_shipping_lee_envios();
		foreach ( $envios_apg as $envio ) {
			$metodo = "apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envio );
			$metodos[] = $metodo;
		}
	
		if ( !$limpieza_free && isset( $_POST['subtab'] ) ) {
			apg_free_shipping_limpiamos_opciones();
		}
		
		//Reordenamos los medios de envío en WooCommerce
		$envios_woocommerce = (array) get_option( 'woocommerce_shipping_method_order' );
		$orden = array_keys( $envios_woocommerce );
	
		$valor_orden = array();
    	foreach ( $orden as $clave ) {
			if ( preg_match( '/apg_free_shipping_/', $clave ) ) {
				$valor_orden[$clave] = $envios_woocommerce[$clave];
			}
    	}
		
		$ordena_envios = array();
		foreach ( $envios_apg as $clave => $envio ) {
			if ( isset( $envios_woocommerce["apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envio )] ) ) {
				$ordena_envios["apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envio )] = $envios_woocommerce["apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envio )];
			} else {
				$ordena_envios["apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envio )] = count( $metodos ) - 1;
			}
		}
		$contador = 0;
		foreach ( $ordena_envios as $clave => $orden ) {
			if ( $contador == 0 ) {
				if ( reset( $valor_orden ) != $orden ) {
					$envios_woocommerce[$clave] = current( $valor_orden );
				}
			} else {
				if ( next( $valor_orden ) != $orden ) {
					$envios_woocommerce[$clave] = current( $valor_orden );
				}
			}
			$contador++;
		}
		asort( $envios_woocommerce );
		update_option( 'woocommerce_shipping_method_order', $envios_woocommerce );	
			
		return $metodos;
	}
	add_filter( 'woocommerce_shipping_methods', 'apg_free_shipping_anade_gastos_de_envio', 10 );
	
	//Recomponemos los nombres de las secciones
	function apg_free_shipping_secciones( $secciones ) {
		foreach ( apg_free_shipping_lee_envios() as $envio ) {
			$limpio = apg_free_shipping_limpia_guiones( $envio );
			if ( $secciones["apg_free_shipping_" . $limpio] != $envio ) {
				$secciones["apg_free_shipping_" . $limpio] = $envio;
			}
		}

		return $secciones;
	}
	add_filter( 'woocommerce_get_sections_shipping', 'apg_free_shipping_secciones' );

	//Controlamos las opciones de WooCommerce para mantenerlas limpias
	function apg_free_shipping_limpiamos_opciones( $limpia = false ) {
		global $limpieza_free;
		
		$apg_opciones = $encontrados = array();
	
		//Vemos las opciones que existen
		foreach ( wp_load_alloptions() as $nombre => $valor ) {
			if ( stristr( $nombre, 'woocommerce_apg_free_shipping_' ) ) {
				$apg_opciones[] = $nombre;
			}
		}
		
		//Vemos las opciones que usamos
		$envios = (array) apg_free_shipping_lee_envios();
		$encontrados[] = "woocommerce_apg_free_shipping_settings";
		foreach ( $envios as $envio ) {
			foreach ( $apg_opciones as $opcion ) {
				if ( strpos( $opcion, apg_free_shipping_limpia_guiones( $envio ) ) !== false ) {
					$encontrados[] = apg_free_shipping_limpia_guiones( $opcion );
				}
			}
		}
		
		//Borramos las no necesarias
		$borrar = ( !$limpia ) ? array_diff( $apg_opciones, $encontrados ) : $apg_opciones;
		foreach( $borrar as $borrame ) {
			if ( preg_match( '/woocommerce_apg_free_shipping_(\d)_settings/', $borrame, $valor ) ) {
				update_option( "woocommerce_apg_free_shipping_" . apg_free_shipping_limpia_guiones( $envios[($valor[1] - 1)] ) . "_settings", get_option( $borrame ) );
			}
			delete_option( $borrame );
		}
		
		$limpieza_free = true; //Cambiamos la variable global para que sólo se ejecute una vez
	}
	
	//Añade un nuevo campo a Opciones de envío para añadir nuevos gastos de envío
	function apg_free_shipping_nuevos_gastos_de_envio( $configuracion ) {
		$anadir_seccion = array();
	
		foreach ( $configuracion as $seccion ) {
			if ( ( isset( $seccion['id'] ) && $seccion['id'] == 'shipping_options' ) && ( isset( $seccion['type'] ) && $seccion['type'] == 'sectionend' ) ) {
				$anadir_seccion[] = array(
					'type'		=> 'sectionend',
					'id'		=> 'shipping_methods' 
				);
				$anadir_seccion[] = array( 
					'title'		=> __( 'WooCommerce - APG Free Postcode/State/Country Shipping', 'apg_free_shipping' ),
					'type'		=> 'title',
					'id'		=> 'apg_free_shipping',
				);
				$anadir_seccion[] = array( 
					'name'		=> __( 'Additional Free Shipping', 'apg_free_shipping' ),
					'desc_tip'	=> __( 'List additonal shipping classes below (1 per line). This is in addition to the default <code>APG Free shipping</code>.', 'apg_free_shipping' ),
					'id'		=> 'campos_apg_free_shipping',
					'type'		=> 'shipping_apg_free_shipping_envios',
				);
				//Este lo usamos para rellenar
				$anadir_seccion[] = array(
					'name'		=> __( 'Additional Free Shipping', 'apg_free_shipping' ),
					'desc_tip'	=> __( 'List additonal shipping classes below (1 per line). This is in addition to the default <code>APG Free shipping</code>.', 'apg_free_shipping' ),
					'id'		=> 'woocommerce_apg_free_shipping',
					'type'		=> 'textarea',
					'default'	=> '',
					'class'		=> 'borrame_apg_free_shipping',					
				);
				$anadir_seccion[] = array(
					'type'		=> 'sectionend',
					'id'		=> 'apg_free_shipping' 
				);
			}
	
			$anadir_seccion[] = $seccion;
		}
		
		return $anadir_seccion;
	}
	add_filter( 'woocommerce_shipping_settings', 'apg_free_shipping_nuevos_gastos_de_envio' );
	
	//Añade un nuevo campo a Opciones de envío para añadir nuevos gastos de envío
	function apg_free_shipping_campos_nuevos_gastos_de_envio( $opciones ) {
		include( 'includes/formulario-gastos-de-envio.php' );
	}
	add_filter( 'woocommerce_admin_field_shipping_apg_free_shipping_envios', 'apg_free_shipping_campos_nuevos_gastos_de_envio' );
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

//Carga las hojas de estilo
function apg_free_shipping_muestra_mensaje() {
	wp_register_style( 'apg_free_shipping_hoja_de_estilo', plugins_url( 'assets/css/style.css', __FILE__ ) ); //Carga la hoja de estilo
	wp_enqueue_style( 'apg_free_shipping_hoja_de_estilo' ); //Carga la hoja de estilo global
	wp_register_style( 'apg_free_shipping_hoja_de_estilo_shipping', plugins_url( 'assets/css/style-shipping.css', __FILE__ ) );
	wp_enqueue_style( 'apg_free_shipping_hoja_de_estilo_shipping' ); //Carga la hoja de estilo global
}
add_action( 'admin_init', 'apg_free_shipping_muestra_mensaje' );

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_free_shipping_desinstalar() {
	delete_option( 'woocommerce_apg_free_shipping_settings' );
	delete_transient( 'apg_free_shipping_plugin' );
}
register_uninstall_hook( __FILE__, 'apg_free_shipping_desinstalar' );
?>
