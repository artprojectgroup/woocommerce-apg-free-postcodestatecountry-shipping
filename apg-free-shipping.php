<?php
/*
Plugin Name: WC - APG Free Shipping
Requires Plugins: woocommerce
Version: 3.2.0.2
Plugin URI: https://wordpress.org/plugins/woocommerce-apg-free-postcodestatecountry-shipping/
Description: Add to WooCommerce a free shipping based on the order postcode, province (state) and country of customer's address and minimum order a amount and/or a valid free shipping coupon. Created from <a href="https://profiles.wordpress.org/artprojectgroup/" target="_blank">Art Project Group</a> <a href="https://wordpress.org/plugins/woocommerce-apg-weight-and-postcodestatecountry-shipping/" target="_blank"><strong>WC - APG Weight Shipping</strong></a> plugin and the original WC_Shipping_Free_Shipping class from <a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><strong>WooCommerce - excelling eCommerce</strong></a>.
Author URI: https://artprojectgroup.es/
Author: Art Project Group
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9
WC requires at least: 5.6
WC tested up to: 10.0.2

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
define( 'VERSION_apg_free_shipping', '3.2.0.2' );

//Funciones generales de APG
include_once( 'includes/admin/funciones-apg.php' );

//¿Está activo WooCommerce?
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_network_only_plugin( 'woocommerce/woocommerce.php' ) ) {
    //Añade compatibilidad con HPOS
    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );

    //Contine la clase que crea los nuevos gastos de envío
	function apg_free_shipping_inicio() {
		if ( ! class_exists( 'WC_Shipping_Method' ) ) {
			return;
		}
		
		//Cargamos funciones necesarias
		include_once( 'includes/admin/funciones.php' );

		#[AllowDynamicProperties]
		class WC_apg_free_shipping extends WC_Shipping_Method {				
			public $categorias_de_producto   = [];
			public $etiquetas_de_producto    = [];
			public $clases_de_envio          = [];
			public $roles_de_usuario         = [];
			public $metodos_de_envio         = [];
			public $metodos_de_pago          = [];
            public $atributos                = [];

			public function __construct( $instance_id = 0 ) {
				$this->id					= 'apg_free_shipping';
				$this->instance_id			= absint( $instance_id );
				$this->method_title			= __( 'APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' );
				$this->method_description	= __( 'Lets you add a free shipping based on Postcode/State/Country of the cart and minimum order a amount and/or a valid free shipping coupon.', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '<span class="apg-weight-marker"></span>';
				$this->supports				= [
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				];
				$this->init();
			}

			//Inicializa los datos
	        public function init() {
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
                    'atributos_excluidos',
                    'tipo_atributos',
					'clases_excluidas',
					'tipo_clases',
					'roles_excluidos',
					'tipo_roles',
					'pago',
                    'envio',
					'icono',
					'muestra_icono',
					'entrega',
					'muestra',
				];
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$campos[] = 'activo';
				}
				if ( version_compare( WC_VERSION, '3.3', '>=' ) ) {
					$campos[] = 'impuestos';
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
                            $( document ).on( 'mouseover', '.wc-shipping-zone-method-settings', function() {
                                if ( $( this ).closest( 'tr' ).find( '.wc-shipping-zone-method-type' ).text() == 'APG Free Shipping' ) {
                                    $( this ).removeClass( 'wc-shipping-zone-method-settings' );
                                }
                            } );
                        } );
					" );
				}

				return parent::get_instance_form_fields();
			}
			
			//Obtiene todos los datos necesarios
			public function apg_free_shipping_obtiene_datos() {
				$this->apg_free_shipping_dame_datos_de_producto( 'categorias_de_producto' ); //Obtiene todas las categorías de producto
				$this->apg_free_shipping_dame_datos_de_producto( 'etiquetas_de_producto' ); //Obtiene todas las etiquetas de producto
				$this->apg_free_shipping_dame_clases_de_envio(); //Obtiene todas las clases de envío
				$this->apg_free_shipping_dame_roles_de_usuario(); //Obtiene todos los roles de usuario
				$this->apg_free_shipping_dame_metodos_de_envio(); //Obtiene todas los métodos de envío
                $this->apg_free_shipping_dame_metodos_de_pago(); //Obtiene todos los métodos de pago
				$this->apg_free_shipping_dame_atributos(); //Obtiene todos los atributos
			}
	
			//Función que lee y devuelve las categorías/etiquetas de producto
			public function apg_free_shipping_dame_datos_de_producto( $tipo ) {
                if ( ! in_array( $tipo, [ 'categorias_de_producto', 'etiquetas_de_producto' ], true ) ) {
                    return;
                }

                //Tipo de taxonomía
                $taxonomy   = ( $tipo === 'categorias_de_producto' ) ? 'product_cat' : 'product_tag';
                $transient  = 'apg_shipping_' . $taxonomy;

                //Obtiene las taxonomías desde la caché
                $this->{$tipo}  = get_transient( $transient );

                if ( empty( $this->{$tipo} ) ) {
                    $argumentos = [
                        'taxonomy'      => $taxonomy,
                        'orderby'       => 'name',
                        'show_count'    => 0,
                        'pad_counts'    => 0,
                        'hierarchical'  => 1,
                        'title_li'      => '',
                        'hide_empty'    => false,
                    ];

                    $datos          = get_categories( $argumentos );
                    $this->{$tipo}  = [];

                    foreach ( $datos as $dato ) {
                        $this->{$tipo}[ $dato->term_id ] = $dato->name;
                    }

                    set_transient( $transient, $this->{$tipo}, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}

			//Función que lee y devuelve los tipos de clases de envío
			public function apg_free_shipping_dame_clases_de_envio() {
                //Obtiene las clases de envío desde la caché
                $clases_de_envio = get_transient( 'apg_shipping_clases_envio' );

                if ( empty( $clases_de_envio ) || ! isset( $clases_de_envio[ 'clases' ], $clases_de_envio[ 'tarifas' ] ) ) {
                    $clases                         = WC()->shipping->get_shipping_classes();
                    $this->clases_de_envio          = [];
                    $this->clases_de_envio_tarifas  = '';

                    if ( ! empty( $clases ) ) {
                        foreach ( $clases as $clase_de_envio ) {
                            $slug   = esc_attr( $clase_de_envio->slug );
                            $name   = $clase_de_envio->name;
                            
                            $this->clases_de_envio[ $slug ] = $name;
                            $this->clases_de_envio_tarifas  .= $slug . ' -> ' . $name . ', ';                            
                        }
                        // Elimina la última coma y añade punto final
                        $this->clases_de_envio_tarifas  = rtrim( $this->clases_de_envio_tarifas, ', ' ) . '.';
                    } else {
                        $this->clases_de_envio[]        = __( 'Select a class&hellip;', 'woocommerce-apg-free-postcodestatecountry-shipping' );
                        $this->clases_de_envio_tarifas  = '';
                    }

                    //Guarda en caché el array completo
                    $clases_de_envio = [
                        'clases'    => $this->clases_de_envio,
                        'tarifas'   => $this->clases_de_envio_tarifas,
                    ];
                    set_transient( 'apg_shipping_clases_envio', $clases_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                } else {
                    $this->clases_de_envio          = $clases_de_envio[ 'clases' ];
                    $this->clases_de_envio_tarifas  = $clases_de_envio[ 'tarifas' ];
                }
			}
			
			//Función que lee y devuelve los roles de usuario
			public function apg_free_shipping_dame_roles_de_usuario() {
                //Obtiene los roles de usuario desde la caché
                $this->roles_de_usuario = get_transient( 'apg_shipping_roles_usuario' );

                if ( empty( $this->roles_de_usuario ) ) {
                    $wp_roles               = new WP_Roles();
                    $this->roles_de_usuario = [];

                    if ( isset( $wp_roles->role_names ) && is_array( $wp_roles->role_names ) ) {
                        foreach ( $wp_roles->role_names as $rol => $nombre ) {
                            $this->roles_de_usuario[ $rol ] = $nombre;
                        }
                    }

                    set_transient( 'apg_shipping_roles_usuario', $this->roles_de_usuario, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}
            
			//Función que lee y devuelve los métodos de envío
            public function apg_free_shipping_dame_metodos_de_envio() {
                global $zonas_de_envio, $wpdb;
                
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- No se puede usar nonce en este contexto (lectura segura con absint)
                $instancia  = isset( $_REQUEST[ 'instance_id' ] ) ? absint( wp_unslash( $_REQUEST[ 'instance_id' ] ) ) : absint( $this->instance_id );
                
                if ( ! $instancia || ! function_exists( 'WC' ) ) {
                    return;
                }
                
                //Obtiene los métodos de envío desde la caché
                $cache_key              = 'apg_shipping_metodos_envio_' . $instancia;
                $this->metodos_de_envio = get_transient( $cache_key );

                if ( empty( $this->metodos_de_envio ) ) {
                    $this->metodos_de_envio = [];
                    //Obtiene la zona de envío de esta instancia
                    $zona_de_envio          = wp_cache_get( "apg_zone_{$instancia}" );
                    if ( false === $zona_de_envio ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- No existe una función alternativa en WooCommerce
                        $zona_de_envio  = $wpdb->get_var( $wpdb->prepare( "SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE instance_id = %d LIMIT 1;", $instancia ) );
                        wp_cache_set( "apg_zone_{$instancia}", $zona_de_envio );
                    }

                    //Recorre zonas cacheadas
                    if ( ! empty( $zona_de_envio ) && is_array( $zonas_de_envio ) ) {
                        foreach ( $zonas_de_envio as $zona ) {
                            if ( ( int ) $zona[ 'id' ] === ( int ) $zona_de_envio && !empty( $zona[ 'shipping_methods' ] ) ) {
                                foreach ( $zona[ 'shipping_methods' ] as $metodo ) {
                                    if ( is_array( $metodo ) && isset( $metodo[ 'instance_id' ] ) && $metodo[ 'instance_id' ] != $instancia ) {
                                        $this->metodos_de_envio[ $metodo[ 'instance_id' ] ] = $metodo[ 'title' ];
                                    }
                                }
                            }
                        }
                    }

                    set_transient( $cache_key, $this->metodos_de_envio, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
                }
			}

            //Función que lee y devuelve los métodos de pago
			public function apg_free_shipping_dame_metodos_de_pago() {
                //Obtiene los métodos de pago desde la caché
                $this->metodos_de_pago  = get_transient( 'apg_shipping_metodos_de_pago' );

                if ( empty( $this->metodos_de_pago ) ) {
                    //Obtiene los métodos de pago
                    global $medios_de_pago;
                    $this->metodos_de_pago  = [];
                    if ( is_array( $medios_de_pago ) && ! empty( $medios_de_pago ) ) {
                        foreach( $medios_de_pago as $id => $titulo ) {
                            $this->metodos_de_pago[ $id ] = $titulo;
                        }
                    }

                    //Guarda la caché durante un mes
                    set_transient( 'apg_shipping_metodos_de_pago',  $this->metodos_de_pago, 30 * DAY_IN_SECONDS );
                }
			}

            //Función que lee y devuelve los atributos
			public function apg_free_shipping_dame_atributos() {
                //Obtiene los atributos desde la caché
                $atributos  = get_transient( 'apg_shipping_atributos' );
                
                if ( is_array( $atributos ) && ! empty( $atributos ) ) {
                    $this->atributos    = $atributos;
                    return;
                }
                
                //Obtiene los atributos
                $atributos  = [];
                $taxonomias = wc_get_attribute_taxonomies();
                if ( !empty( $taxonomias ) && is_array( $taxonomias ) ) {
                    foreach ( $taxonomias as $atributo ) {
                        if ( empty( $atributo->attribute_name ) || empty( $atributo->attribute_label ) ) {
                            continue;
                        }
                        
                        $nombre_taxonomia = 'pa_' . $atributo->attribute_name;
                        $terminos         = get_terms( [ 'taxonomy' => $nombre_taxonomia, 'hide_empty' => false ] );

                        if ( ! is_wp_error( $terminos ) && ! empty( $terminos ) ) {
                            foreach ( $terminos as $termino ) {
                                $atributos[ esc_attr( $atributo->attribute_label ) ][ $nombre_taxonomia . '-' . $termino->slug ] = $termino->name;
                            }
                        }
                    }
                }

                $this->atributos = $atributos;

                set_transient( 'apg_shipping_atributos', $atributos, 30 * DAY_IN_SECONDS ); //Guarda la caché durante un mes
			}
			
			//Habilita el envío
			public function is_available( $paquete ) {
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					if ( $this->activo == 'no' ) {
						return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this ); //No está activo
					}
				} else {
					if ( ! $this->is_enabled() ) {
						return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this ); //No está activo
					}
				}
				
				//Recoge los datos
				$this->apg_free_shipping_obtiene_datos();

				//Comprobamos los roles excluidos
                $validacion = true;
                if ( ! empty( $this->roles_excluidos ) ) {
					if ( empty( wp_get_current_user()->roles ) ) {
                        if ( ( in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'no' ) ||
                            ( ! in_array( 'invitado', $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) { //Usuario invitado
                            $validacion = false; //Role excluido
                        } else {
                            $validacion = true;
                        }                   
                    } else {
                        foreach( wp_get_current_user()->roles as $rol ) { //Usuario con rol
                            if ( ( in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'no' ) || 
                            ( ! in_array( $rol, $this->roles_excluidos ) && $this->tipo_roles == 'yes' ) ) {
                                $validacion = false; //Role excluido
                            } else {
                                $validacion = true;
                            } 
                        }
                    }             
				}
                if ( ! $validacion ) {
                    return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this ); //No está activo
                }

				//Variable
				$total_excluido	= 0;

				//Toma distintos datos de los productos
				foreach ( WC()->cart->get_cart() as $identificador => $valores ) {
					$producto = $valores[ 'data' ];

					//Comprobamos las categorías de producto excluidas
					if ( ! empty( $this->categorias_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( ! empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $parent->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
							}
						} else {
							if ( ( ! empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'no' ) || 
								( empty( array_intersect( $producto->get_category_ids(), $this->categorias_excluidas ) ) && $this->tipo_categorias == 'yes' ) ) {
								return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
							}
						}
					}

					//Comprobamos las etiquetas de producto excluidas
					if ( ! empty( $this->etiquetas_excluidas ) ) {
						if ( $producto->is_type( 'variation' ) ) {
							$parent = wc_get_product( $producto->get_parent_id() );
							if ( ( ! empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $parent->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
							}
						} else {
							if ( ( ! empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'no' ) || 
								( empty( array_intersect( $producto->get_tag_ids(), $this->etiquetas_excluidas ) ) && $this->tipo_etiquetas == 'yes' ) ) {
								return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
							}
						}
					}

                    //No atiende a las atributos excluidos
					if ( ! empty( $this->atributos_excluidos ) ) {
                        $atributos_excluidos    = [];
                        foreach ( $this->atributos_excluidos as $atributos ) {
                            $atributos                              = explode( "-", $atributos );
                            $atributos_excluidos[ $atributos[ 0 ] ] = $atributos[ 1 ]; 
                        }
                        
                        if ( ( ! empty( array_intersect_assoc( $producto->get_attributes(), $atributos_excluidos ) ) && $this->tipo_atributos == 'no' ) || 
                            ( empty( array_intersect_assoc( $producto->get_attributes(), $atributos_excluidos ) ) && $this->tipo_atributos == 'yes' ) ) {
                            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
                        }
					}

                    //No atiende a las clases de envío excluidas
					if ( ! empty( $this->clases_excluidas ) ) {
						//Clase de envío
						if ( ( in_array( $producto->get_shipping_class(), $this->clases_excluidas ) || ( in_array( "todas", $this->clases_excluidas ) && $producto->get_shipping_class() ) ) && $this->tipo_clases == 'no' ) {
							$this->reduce_valores( $total_excluido, $producto, $valores );
							
							continue;
						} else if ( ! in_array( $producto->get_shipping_class(), $this->clases_excluidas ) && ! in_array( "todas", $this->clases_excluidas ) && $this->tipo_clases == 'yes' ) {
							return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $paquete, $this );
						}
					}
				}
	
				//Variables
				$habilitado				= false;
				$tiene_cupon			= false;
				$tiene_importe_minimo	= false;
				
				//Cupón
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
				
				//Requisitos
				if ( in_array( $this->requires, [ 'importe_minimo', 'cualquiera', 'ambos' ] ) ) {
					$total = WC()->cart->get_displayed_subtotal();
                                        
					if ( version_compare( WC_VERSION, '3.2', '<' ) && isset( WC()->cart->cart_contents_total ) ) {
						$total = ( 'incl' === WC()->cart->tax_display_cart ) ? round( $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() ), wc_get_price_decimals() ) : round( $total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals() );
					} elseif ( version_compare( WC_VERSION, '4.4', '<' ) ) {
                        $total = ( 'incl' === WC()->cart->tax_display_cart ) ? round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() ) : round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
                    } else {
                        $total = ( 'incl' === WC()->cart->get_tax_price_display_mode() ) ? round( $total - ( WC()->cart->get_discount_total() + WC()->cart->get_discount_tax() ), wc_get_price_decimals() ) : round( $total - WC()->cart->get_discount_total(), wc_get_price_decimals() );
                    }

                    //¿Impuestos?
                    if ( version_compare( WC_VERSION, '3.3', '>=' ) && $this->impuestos == "yes" && ! WC()->cart->display_prices_including_tax() ) {
                        $total  = $total + WC()->cart->get_subtotal_tax() - WC()->cart->get_cart_discount_tax_total();
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
				
				return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $habilitado, $paquete, $this );
			}
			
			//Calcula el gasto de envío
			public function calculate_shipping( $paquete = [] ) {
				//Comprobamos si está activo WPML para coger la traducción correcta de la clase de envío
				if ( function_exists('icl_object_id') && ! function_exists( 'pll_the_languages' ) ) {
					global $sitepress;
					do_action( 'wpml_switch_language', $sitepress->get_default_language() );
				}
				
				//Comprobamos si está activo WPML para devolverlo al idioma que estaba activo
				if ( function_exists('icl_object_id') && ! function_exists( 'pll_the_languages' ) ) {
					do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
				}
				
				$this->add_rate( [
					'id'		=> $this->get_rate_id(),
					'label'		=> $this->title,
					'cost'		=> 0,
					'taxes'		=> false
				] );
                
                //Limpieza del transient del icono para evitar datos obsoletos
                delete_transient( 'apg_shipping_icono_' . $this->instance_id );
			}
			
			//Reduce valores en categorías, etiquetas y clases de envío excluídas
			public function reduce_valores( &$total_excluido, $producto, $valores ) {
				if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
					$total_excluido = ( WC()->cart->tax_display_cart == 'excl' ) ? $total_excluido + $producto->get_price_excluding_tax() * $valores[ 'quantity' ] : $total_excluido + $producto->get_price_including_tax() * $valores[ 'quantity' ];
				} elseif ( version_compare( WC_VERSION, '4.4', '<' ) ) {
                    $total_excluido = ( WC()->cart->tax_display_cart == 'excl' ) ? $total_excluido + wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : $total_excluido + wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];
                } else {
                    $total_excluido = ( WC()->cart->get_tax_price_display_mode() == 'excl' ) ? $total_excluido + wc_get_price_excluding_tax( $producto ) * $valores[ 'quantity' ] : $total_excluido + wc_get_price_including_tax( $producto ) * $valores[ 'quantity' ];
				}
			}
		}
	}
	add_action( 'plugins_loaded', 'apg_free_shipping_inicio', 0 );
} else {
	add_action( 'admin_notices', 'apg_free_shipping_requiere_wc' );
}

//Añade soporte a Checkout y Cart Block
function apg_free_shipping_script_bloques() {
    //Evita ejecución en backend/editor REST
    if ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) ) {
        return; 
    }
    
	//Detecta bloques de WooCommerce para carrito o checkout
	$bloques   = function_exists( 'has_block' ) && ( has_block( 'woocommerce/cart', wc_get_page_id( 'cart' ) ) || has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) );

	if ( ! $bloques ) {
        return; //No se están usando bloques de carrito/checkout
	}

    $script_handle  = 'apg-shipping-bloques';
    if ( ! wp_script_is( $script_handle, 'enqueued' ) ) {
        wp_enqueue_script( $script_handle, plugins_url( 'assets/js/apg-free-shipping-bloques.js', DIRECCION_apg_free_shipping ), [ 'jquery' ], VERSION_apg_free_shipping, true );
        wp_localize_script( $script_handle, 'apg_shipping', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
    }
}
add_action( 'enqueue_block_assets', 'apg_free_shipping_script_bloques' );

//Añade la etiqueta a los bloques
function apg_free_shipping_ajax_datos() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
    $metodo = isset( $_POST[ 'metodo' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'metodo' ] ) ) : '';
    if ( ! preg_match( '/^([a-zA-Z0-9_]+):(\d+)$/', $metodo, $method ) ) {
        wp_send_json_error( __( 'Invalid format', 'woocommerce-apg-free-postcodestatecountry-shipping' ) );
    }

    list( , $slug, $instance_id )   = $method;
    $opciones                       = get_option( "woocommerce_{$slug}_{$instance_id}_settings" );
    if ( ! is_array( $opciones ) ) {
        wp_send_json_error( __( 'No data available', 'woocommerce-apg-free-postcodestatecountry-shipping' ) );
    }
    
	//Tiempo de entrega
    $entrega    = $opciones[ 'entrega' ] ?? '';
	if ( ! empty( $entrega ) ) {
        // translators: %s is the estimated delivery time (e.g., "24-48 hours").
        $entrega    = ( apply_filters( 'apg_free_shipping_delivery', true ) ) ? sprintf( __( "Estimated delivery time: %s", 'woocommerce-apg-free-postcodestatecountry-shipping' ), $entrega ) : $entrega;
    }
    wp_send_json_success( [
        'titulo'    => $opciones[ 'title' ] ?? ucfirst( $slug ),
        'entrega'   => wp_kses_post( $entrega ),
        'icono'     => esc_url_raw( $opciones[ 'icono' ] ?? '' ),
        'muestra'   => $opciones[ 'muestra_icono' ] ?? '',
    ] );
}
add_action( 'wp_ajax_apg_free_shipping_ajax_datos', 'apg_free_shipping_ajax_datos' );
add_action( 'wp_ajax_nopriv_apg_free_shipping_ajax_datos', 'apg_free_shipping_ajax_datos' );

//Muestra el mensaje de activación de WooCommerce y desactiva el plugin
function apg_free_shipping_requiere_wc() {
	global $apg_free_shipping;
		
    echo '<div class="error fade" id="message">';
    echo '<h3>' . esc_html( $apg_free_shipping[ 'plugin' ] ) . '</h3>';
    echo '<h4>' . esc_html__( 'This plugin requires WooCommerce to be active in order to run!', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</h4>';
    echo '</div>';
	deactivate_plugins( DIRECCION_apg_free_shipping );
}

//Eliminamos todo rastro del plugin al desinstalarlo
function apg_free_shipping_desinstalar() {
    global $wpdb;
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Limpieza forzada de opciones temporales propias del plugin
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%woocommerce_apg_free_shipping_%'" );
}
register_uninstall_hook( __FILE__, 'apg_free_shipping_desinstalar' );
