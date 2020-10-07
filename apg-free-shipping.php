<?php
/*
Plugin Name: WC - APG Free Shipping
Version: 2.4.0.7
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province (state) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="https://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WC - APG Weight Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
Requires at least: 3.8
Tested up to: 5.6
WC requires at least: 2.6
WC tested up to: 4.6

Text Domain: woocommerce-apg-free-postcodestatecountry-shipping
Domain Path: /languages

@package WC - APG Free Shipping
@category Core
@author Art Project Group
*/

//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

//Definimos constantes
define( 'DIRECCION_apg_free_shipping', plugin_basename( __FILE__ ) );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
	//Contine la clase que crea los nuevos gastos de envío
	function apg_free_shipping_inicio() {
		if ( !class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}
		
		//Cargamos funciones necesarias
		include_once( 'includes/admin/funciones.php' );

		class WC_apg_free_shipping extends WC_Shipping_Method {				
			public $categorias_de_producto	= [];
			public $etiquetas_de_producto	= [];
			public $clases_de_envio			= [];
			public $roles_de_usuario		= [];
			public $metodos_de_pago			= [];

			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_free_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( 'APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you add a free shipping based on Postcode/State/Country of the cart and minimum order a amount and/or a valid free shipping coupon.', 'woocommerce-apg-free-postcodestatecountry-shipping' );
				$this->supports				= [
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				];
				$this->init();
			}

			//Inicializa los datos
	        public function init() {
				$this->apg_free_shipping_dame_datos_de_producto( 'categorias_de_producto' ); //Obtiene todas las categorías de producto
				$this->apg_free_shipping_dame_datos_de_producto( 'etiquetas_de_producto' ); //Obtiene todas las etiquetas de producto
				$this->apg_free_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío
				$this->apg_free_shipping_dame_roles_de_usuario(); //Obtiene todos los roles de usuario
				$this->apg_free_shipping_dame_metodos_de_pago(); //Obtiene todos los métodos de pago
	
				$this->init_form_fields();
				$this->init_settings();

				//Inicializamos variables
				$campos = [ 
					'title', 
					'requires', 
					'importe_minimo', 
					'peso',
					'categorias_excluidas',
					'tipo_categorias',
					'etiquetas_excluidas',
					'tipo_etiquetas',
					'clases_excluidas',
					'tipo_clases',
					'roles_excluidos',
					'tipo_roles',
					'pago',
					'icono',
					'muestra_icono',
					'entrega',
					'muestra',
				];
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$campos[ 'activo' ];
				}
				foreach ( $campos as $campo ) {
					$this->$campo = $this->get_option( $campo );
				}
				
				//Acción
				add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
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
							$( document ).on( 'mouseover', '.wc-shipping-zone-method-rows', function() {
								$( 'a.wc-shipping-zone-method-settings' ).removeClass( 'wc-shipping-zone-method-settings' );
							} );
						} );
					" );
				}

				return parent::get_instance_form_fields();
			}
	
			//Función que lee y devuelve las categorías/etiquetas de producto
			public function apg_free_shipping_dame_datos_de_producto( $tipo ) {
				$taxonomy = ( $tipo == 'categorias_de_producto' ) ? 'product_cat' : 'product_tag';
				
				$argumentos = [
					'taxonomy'		=> $taxonomy,
					'orderby'		=> 'name',
					'show_count'	=> 0,
					'pad_counts'	=> 0,
					'hierarchical'	=> 1,
					'title_li'		=> '',
					'hide_empty'	=> 0
				];
				$datos = get_categories( $argumentos );
				
				foreach ( $datos as $dato ) {
					$this->{$tipo}[ $dato->term_id ] = $dato->name;
				}
			}

			//Función que lee y devuelve los tipos de clases de envío
			public function apg_free_shipping_dame_clases_de_envio() {
				if ( WC()->shipping->get_shipping_classes() ) {
					foreach ( WC()->shipping->get_shipping_classes() as $clase_de_envio ) {
						$this->clases_de_envio[ esc_attr( $clase_de_envio->slug ) ] = $clase_de_envio->name;
					}
				} else {
					$this->clases_de_envio[ ] = __( 'Select a class&hellip;', 'woocommerce-apg-free-postcodestatecountry-shipping' );
				}
			}
			
			//Función que lee y devuelve los roles de usuario
			public function apg_free_shipping_dame_roles_de_usuario() {
				$wp_roles = new WP_Roles();

				foreach( $wp_roles->role_names as $rol => $nombre ) {
					$this->roles_de_usuario[ $rol ] = $nombre;
				}				
			}	

			//Función que lee y devuelve los métodos de pago
			public function apg_free_shipping_dame_metodos_de_pago() {
				$medios_de_pago = WC()->payment_gateways->payment_gateways();
				
                if ( is_array( $medios_de_pago ) && !empty( $medios_de_pago ) ) {
                    foreach( $medios_de_pago as $clave => $medio_de_pago ) {
                        $this->metodos_de_pago[ $medio_de_pago->id ] = $medio_de_pago->title;
                    }
                }
			}
	
			//Calcula el gasto de envío
			public function calculate_shipping( $paquete = [] ) {
				$this->add_rate( [
					'id'		=> $this->get_rate_id(),
					'label'		=> $this->title,
					'cost'		=> 0,
					'taxes'		=> false
				] );
			}
			
			//Reduce valores en categorías, etiquetas y clases de envío excluídas
			public function reduce_valores( &$total_excluido, $producto, $valores ) {
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$total_excluido = ( WC()->cart->tax_display_cart == 'excl' ) ? $total_excluido + $producto->get_price_excluding_tax() * $valores[ 'quantity' ] : $total_excluido + $producto->get_price_including_tax() * $valores[ 'quantity' ];
				} else {
					$total_excluido = ( WC()->cart->get_tax_price_display_mode() == 'excl' ) ? $total_excluido + wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : $total_excluido + wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];
				}
			}
			
			//Habilita el envío
			public function is_available( $paquete ) {
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					if ( $this->activo == 'no' ) {
						return false; //No está activo
					}
				} else {
					if ( ! $this->is_enabled() ) {
						return false; //No está activo
					}
				}
				
				//Comprobamos los roles excluidos
				if ( !empty( $this->roles_excluidos ) ) {
					if ( empty( wp_get_current_user()->roles ) &&
						( ( in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'no' ) ||
						( !in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) ) { //Usuario invitado
						return false; //Role excluido
					}
					foreach( wp_get_current_user()->roles as $rol ) { //Usuario con rol
						if ( ( in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'no' ) || 
							( !in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) {
							return false; //Role excluido
						}
					}
				}

				//Variable
				$total_excluido = 0;
				
				//Comprobamos si está activo WPML para coger la traducción correcta de la clase de envío
				if ( function_exists('icl_object_id') && !function_exists( 'pll_the_languages' ) ) {
					global $sitepress;
					do_action( 'wpml_switch_language', $sitepress->get_default_language() );
				}

				//Toma distintos datos de los productos
				foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
					$producto = $valores[ 'data' ];

					//Comprobamos las categorías de producto excluidas
					if ( !empty( $this->categorias_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( !empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return false;
							}
						} else {
							if ( ( !empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return false;
							}
						}
					}

					//Comprobamos las etiquetas de producto excluidas
					if ( !empty( $this->etiquetas_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( !empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return false;
							}
						} else {
							if ( ( !empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return false;
							}
						}
					}

					//No atiende a las clases de envío excluidas
					if ( !empty( $this->clases_excluidas ) ) {
						//Clase de envío
						if ( ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || ( in_array( "todas", $this->clases_excluidas ) && $producto->get_shipping_class() ) ) && $this->tipo_clases == 'no' ) {
							$this->reduce_valores( $total_excluido, $producto, $valores );
							
							continue;
						} else if ( !in_array( $producto->get_shipping_class(), $this->clases_excluidas ) && !in_array( "todas", $this->clases_excluidas ) && $this->tipo_clases == 'yes' ) {
							return false;
						}
					}
				}
				
				//Comprobamos si está activo WPML para devolverlo al idioma que estaba activo
				if ( function_exists('icl_object_id') && !function_exists( 'pll_the_languages' ) ) {
					do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
				}

				//Variables
				$habilitado				= false;
				$tiene_cupon			= false;
				$tiene_importe_minimo	= false;
	
				if ( in_array( $this->requires, [ 'cupon', 'cualquiera', 'ambos' ] ) ) {
					if ( $cupones = WC()->cart->get_coupons() ) {
						foreach ( $cupones as $codigo => $cupon ) {
							if ( $cupon->is_valid() && $cupon->enable_free_shipping() ) {
								$tiene_cupon = true;
								break;
							}
						}
					}
				}
	
				if ( in_array( $this->requires, [ 'importe_minimo', 'cualquiera', 'ambos' ] ) ) {
					$total = WC()->cart->get_displayed_subtotal();

					if ( version_compare( WC_VERSION, '3.2', '<' ) && isset( WC()->cart->cart_contents_total ) ) {
						$total = ( 'incl' === WC()->cart->tax_display_cart ) ? round( $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() ), wc_get_price_decimals() ) : round( $total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals() );
					} else {
						$total = ( 'incl' === WC()->cart->get_tax_price_display_mode() ) ? round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() ) : round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
					}

					//Revisa el peso total
					$peso = true;
					if ( $this->peso ) {						
						if ( WC()->cart->cart_contents_weight > $this->peso ) {
							$peso = false;
						}
					}
					
					if ( $total - $total_excluido >= $this->importe_minimo && $peso ) {
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
	
				if ( $this->muestra == 'yes' && $habilitado ) {
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
	
	//Filtra los medios de pago
	function apg_free_shipping_filtra_medios_de_pago( $medios ) {
		if ( isset( WC()->session->chosen_shipping_methods ) ) {
			$id = explode( ":", WC()->session->chosen_shipping_methods[ 0 ] );
		} else if ( isset( $_POST[ 'shipping_method' ] ) ) {
			$id = explode( ":", $_POST[ 'shipping_method' ][ 0 ] );
		}
		if ( !isset( $id[ 1 ] ) ) {
			return $medios;
		}
		$configuracion	= maybe_unserialize( get_option( 'woocommerce_apg_free_shipping_' . $id[ 1 ] .'_settings' ) );
		
		if ( isset( $_POST[ 'payment_method' ] ) && !$medios ) {
			$medios = $_POST[ 'payment_method' ];
		}

		if ( !empty( $configuracion[ 'pago' ] ) && $configuracion[ 'pago' ][ 0 ] != 'todos' ) {
			foreach ( $medios as $nombre => $medio ) {
				if ( is_array( $configuracion[ 'pago' ] ) ) {
					if ( !in_array( $nombre, $configuracion[ 'pago' ] ) ) {
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
} else {
	add_action( 'admin_notices', 'apg_free_shipping_requiere_wc' );
}

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_free_shipping_requiere_wc() {
	global $apg_free_shipping;
		
	echo '<div class="error fade" id="message"><h3>' . $apg_free_shipping[ 'plugin' ] . '</h3><h4>' . __( 'This plugin require WooCommerce active to run!', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</h4></div>';
	deactivate_plugins( DIRECCION_apg_free_shipping );
}

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
