<?php 
/**
 * Definición de los campos del formulario de ajustes de WC - APG Free Shipping.
 *
 * Contiene el array de campos configurables para la instancia del método de envío,
 * incluyendo opciones de título, requisitos, importe mínimo, exclusiones por categorías,
 * etiquetas, atributos, clases de envío, roles de usuario, métodos de pago, métodos de envío,
 * icono, estimación de entrega y otras opciones avanzadas.
 *
 * @package WC-APG-Free-Shipping
 * @subpackage Includes/Admin
 * @author Art Project Group
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

$this->apg_free_shipping_obtiene_datos(); // Recoge los datos.
$apg_ajax_nonce = wp_create_nonce( 'apg_ajax_terms' );

// Campos del formulario.
// translators: %1$s is a context-dependent item name (e.g., product category, tag, attribute, role, or shipping class); %2$s is the shipping method title.
$texto  = __( "Select the %1\$s where %2\$s doesn't accept shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' );
$campos = [];

// Campo: Activar/desactivar (solo WC < 2.7)
if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
	$campos[ 'activo' ] = [ 
		'title'				=> __( 'Enable/Disable', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'				=> 'checkbox',
		'label'				=> __( 'Enable this shipping method', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'			=> 'yes'
	];
}
$campos[ 'title' ] = [ 
	'title' 				=> __( 'Method Title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type' 					=> 'text',
	'description' 			=> __( 'This controls the title which the user sees during checkout.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default'				=> $this->method_title,
	'desc_tip'				=> true,
];
$campos[ 'requires' ] = [ 
	'title' 				=> __( 'Free Shipping Requires...', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type' 					=> 'select',
	'class'					=> 'wc-enhanced-select',
	'options'				=> [ 
		''					=> __( 'N/A', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'cupon'				=> __( 'A valid free shipping coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'importe_minimo'	=> __( 'A minimum order amount (defined below)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'cualquiera'		=> __( 'A minimum order amount OR a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'ambos'				=> __( 'A minimum order amount AND a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	 ],
];
$campos[ 'importe_minimo' ] = [ 
	'title'					=> __( 'Minimum Order Amount', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type'					=> 'price',
	'description' 			=> __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default' 				=> '0',
	'desc_tip'      		=> true,
	'placeholder'			=> wc_format_localized_price( 0 )
];
if ( version_compare( WC_VERSION, '3.3', '>=' ) ) {
    $campos[ 'impuestos' ] = [
        'title'				=> __( 'Including taxes', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
        'type'				=> 'checkbox',
        'label'				=> sprintf( __( "Minimum order amount includes taxes.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        'desc_tip' 			=> sprintf( __( "Check this field if the minimum order amount must include taxes.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        'default'			=> 'no',
    ];
}
$campos[ 'peso' ] = [ 
	'title'					=> __( 'No shipping (Max. weight)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type'					=> 'text',
	'description' 			=> __( 'Users may not add more than this weight to get free shipping (if greater than zero).', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default' 				=> '0',
	'desc_tip'      		=> true,
	'placeholder'			=> wc_format_localized_decimal( 0 ),
	'data_type' 			=> 'decimal',
	'class'					=> 'short wc_input_decimal'
];
// Product categories: preparatory code for AJAX/multiselect/seeded options
$categorias_opts  = is_array( $this->categorias_de_producto ) ? $this->categorias_de_producto : [];
$categorias_cnt   = count( $categorias_opts );
$categorias_ajax  = $categorias_cnt > 500;
$categorias_saved = (array) $this->get_option( 'categorias_excluidas', [] );
$categorias_seed  = [];
if ( $categorias_ajax && ! empty( $categorias_saved ) ) {
	foreach ( $categorias_saved as $cid ) {
		if ( isset( $categorias_opts[ $cid ] ) ) {
			$categorias_seed[ $cid ] = $categorias_opts[ $cid ];
		}
	}
}
$campos[ 'categorias_excluidas' ] = [
    // translators: %s is the name of the product category.
	'title'				=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product category', 'woocommerce-apg-free-postcodestatecountry-shipping' )  ),
    // translators: %1$s is the name of the product category, %2$s is the shipping method title.
    'desc_tip' 			=> sprintf( $texto, __( 'product category', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'				=> 'width: 450px;',
	'default'			=> '',
	'type'				=> 'multiselect',
	'class'				=> 'wc-enhanced-select apg-ajax-select',
	'custom_attributes'	=> $categorias_ajax ? [
		'data-apg-ajax' => '1',
		'data-source'   => 'categories',
		'data-nonce'    => $apg_ajax_nonce,
	] : [],
	'options' => $categorias_ajax ? $categorias_seed : $categorias_opts,
	'description' => ( $categorias_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-free-postcodestatecountry-shipping' ) : '' ),
];
$campos[ 'tipo_categorias' ] = [
    // translators: %s is the name of the product category.
	'type'				=> 'checkbox',
    // translators: %s is the plural "product categories".
	'label'				=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "product categories".
	'desc_tip' 			=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'			=> 'no',
];
// Product tags: preparatory code for AJAX/multiselect/seeded options
$etiquetas_opts  = is_array( $this->etiquetas_de_producto ) ? $this->etiquetas_de_producto : [];
$etiquetas_cnt   = count( $etiquetas_opts );
$etiquetas_ajax  = $etiquetas_cnt > 500;
$etiquetas_saved = (array) $this->get_option( 'etiquetas_excluidas', [] );
$etiquetas_seed  = [];
if ( $etiquetas_ajax && ! empty( $etiquetas_saved ) ) {
	foreach ( $etiquetas_saved as $tid ) {
		if ( isset( $etiquetas_opts[ $tid ] ) ) {
			$etiquetas_seed[ $tid ] = $etiquetas_opts[ $tid ];
		}
	}
}
$campos[ 'etiquetas_excluidas' ] = [
    // translators: %s is the name of the product tag.
	'title'				=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
    // translators: %1$s is the product tag name, %2$s is the shipping method title.
	'desc_tip' 			=> sprintf( $texto, __( 'product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'				=> 'width: 450px;',
	'default'			=> '',
	'type'				=> 'multiselect',
	'class'				=> 'wc-enhanced-select apg-ajax-select',
	'custom_attributes' => $etiquetas_ajax ? [
		'data-apg-ajax' => '1',
		'data-source'   => 'tags',
		'data-nonce'    => $apg_ajax_nonce,
	] : [],
	'options' => $etiquetas_ajax ? $etiquetas_seed : $etiquetas_opts,
	'description' => ( $etiquetas_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-free-postcodestatecountry-shipping' ) : '' ),
];
$campos[ 'tipo_etiquetas' ] = [
    // translators: %s is the name of the product tag.
	'title'				=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'type'				=> 'checkbox',
    // translators: %s is the plural "product tags".
	'label'				=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "product tags".
	'desc_tip' 			=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'			=> 'no',
];
if ( wc_get_attribute_taxonomies() ) {
	// Attributes: preparatory code for AJAX/multiselect/seeded options
	$atributos_opts  = is_array( $this->atributos ) ? $this->atributos : [];
	$atributos_cnt   = count( $atributos_opts );
	$atributos_ajax  = $atributos_cnt > 500;
	$atributos_saved = (array) $this->get_option( 'atributos_excluidos', [] );
	$atributos_seed  = [];
	if ( $atributos_ajax && ! empty( $atributos_saved ) ) {
		foreach ( $atributos_saved as $aid ) {
			if ( isset( $atributos_opts[ $aid ] ) ) {
				$atributos_seed[ $aid ] = $atributos_opts[ $aid ];
			}
		}
	}
    $campos[ 'atributos_excluidos' ] = [
        // translators: %s is the name of the attribute.
        'title'				=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Attribute', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        // translators: %1$s is the attribute name, %2$s is the shipping method title.
        'desc_tip' 			=> sprintf( $texto, __( 'attribute', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
        'css'				=> 'width: 450px;',
        'default'			=> '',
		'type'				=> 'multiselect',
		'class'				=> 'wc-enhanced-select apg-ajax-select',
		'custom_attributes' => $atributos_ajax ? [
			'data-apg-ajax' => '1',
			'data-source'   => 'attributes',
			'data-nonce'    => $apg_ajax_nonce,
		] : [],
		'options' => $atributos_ajax ? $atributos_seed : $atributos_opts,
		'description' => ( $atributos_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-free-postcodestatecountry-shipping' ) : '' ),
    ];
    $campos[ 'tipo_atributos' ] = [
        // translators: %s is the name of the attribute.
        'title'				=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Attribute', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        'type'				=> 'checkbox',
        // translators: %s is the plural "attributes".
        'label'				=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'attributes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        // translators: %s is the plural "attributes".
        'desc_tip' 			=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'attributes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        'default'			=> 'no',
    ];
}
if ( WC()->shipping->get_shipping_classes() ) {
	// Shipping classes: preparatory code for AJAX/multiselect/seeded options
	$clases_opts  = is_array( $this->clases_de_envio ) ? $this->clases_de_envio : [];
	$clases_cnt   = count( $clases_opts );
	$clases_ajax  = $clases_cnt > 500;
	$clases_saved = (array) $this->get_option( 'clases_excluidas', [] );
	$clases_seed  = [];
	if ( $clases_ajax && ! empty( $clases_saved ) ) {
		foreach ( $clases_saved as $sid ) {
			if ( isset( $clases_opts[ $sid ] ) ) {
				$clases_seed[ $sid ] = $clases_opts[ $sid ];
			}
		}
	}
	$campos[ 'clases_excluidas' ] = [
        // translators: %s is the name of the shipping class.
		'title'				=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        // translators: %1$s is the shipping class name, %2$s is the shipping method title.
		'desc_tip' 			=> sprintf( $texto, __( 'shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
		'css'				=> 'width: 450px;',
		'default'			=> '',
		'type'				=> 'multiselect',
		'class'				=> 'wc-enhanced-select apg-ajax-select',
		'custom_attributes' => $clases_ajax ? [
			'data-apg-ajax' => '1',
			'data-source'   => 'classes',
			'data-nonce'    => $apg_ajax_nonce,
		] : [],
		'options' => [ 'todas' => __( 'All enabled shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ] + ( $clases_ajax ? $clases_seed : $clases_opts ),
		'description' => ( $clases_cnt > 500 ? __( 'Large list. Type to search…', 'woocommerce-apg-free-postcodestatecountry-shipping' ) : '' ),
	];
	$campos[ 'tipo_clases' ] = [
        // translators: %s is the name of the shipping class.
		'title'				=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'type'				=> 'checkbox',
        // translators: %s is the plural "shipping classes".
		'label'				=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
        // translators: %s is the plural "shipping classes".
		'desc_tip' 			=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'default'			=> 'no',
	];
}
$campos[ 'roles_excluidos' ] = [ 
    // translators: %s is the name of the user role.
	'title'				=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
    // translators: %1$s is the user role name, %2$s is the shipping method title.
	'desc_tip' 			=> sprintf( $texto, __( 'user role', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'				=> 'width: 450px;',
	'default'			=> '',
	'type'				=> 'multiselect',
	'class'				=> 'wc-enhanced-select',
	'options' 			=> [ 
		'invitado'			=> __( 'Guest', 'woocommerce-apg-free-postcodestatecountry-shipping' ) 
	] + $this->roles_de_usuario,
];
$campos[ 'tipo_roles' ] = [
    // translators: %s is the name of the user role.
	'title'				=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'type'				=> 'checkbox',
    // translators: %s is the plural "user roles".
	'label'				=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
    // translators: %s is the plural "user roles".
	'desc_tip' 			=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'			=> 'no',
];
$campos[ 'pago' ] = [
	'title'				=> __( 'Payment gateway', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
    // translators: %s is the shipping method title.
	'desc_tip'			=> sprintf( __( 'Payment gateway available for %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'				=> 'width: 450px;',
	'default'			=> [ 
		'todos' 
	],
	'type'				=> 'multiselect',
	'class'				=> 'chosen_select',
	'options' 			=> [ 
		'todos'				=> __( 'All enabled payments', 'woocommerce-apg-free-postcodestatecountry-shipping' )
	] + $this->metodos_de_pago,
];
// Shipping methods: always static options, no AJAX, multiselect
$metodos_opts = is_array( $this->metodos_de_envio ) ? $this->metodos_de_envio : [];
if ( ! empty( $this->metodos_de_envio ) ) {
    $campos[ 'envio' ] = [
        'title'			=> __( 'Shipping methods', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
        // translators: %s is the shipping method title.
        'desc_tip'		=> sprintf( __( "Shipping methods available in the same shipping zone of %s", 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
        'css'			=> 'width: 450px;',
        'default'		=> [
            'todos'
        ],
		'type'			=> 'multiselect',
		'class'			=> 'wc-enhanced-select',
		'options' => [
			'todos'			=> __( 'All enabled shipping methods', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'ninguno'		=> __( 'No other shipping methods', 'woocommerce-apg-free-postcodestatecountry-shipping' )
		] + $metodos_opts,
    ];
}
$campos[ 'icono' ] = [ 
		'title'			=> __( 'Icon image', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_free_shipping ),
		'desc_tip'		=> true,
];
$campos[ 'muestra_icono' ] = [ 
		'title'			=> __( 'How show icon image?', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'desc_tip' 		=> __( 'Select how you want to show the icon image.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> [ 
			'no'			=> __( 'Not show, just title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'delante'		=> __( 'Before title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'detras'		=> __( 'After title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'solo'			=> __( 'No title, just icon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		 ],
];
$campos[ 'entrega' ] = [ 
		'title'			=> __( 'Estimated delivery time', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
];
$campos[ 'precio' ] = [ 
        // translators: %s is the shipping price to be shown, typically "0,00 €".
		'title'			=> sprintf( __( 'Show %s price', 'woocommerce-apg-free-postcodestatecountry-shipping' ), wc_price( 0 ) ),
		'type'			=> 'checkbox',
        // translators: %s is the shipping price to be shown after the label, typically "0,00 €".
		'label'			=> sprintf( __( "Show %s price after method title.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), wc_price( 0 ) ),
		'default'		=> 'no',
];
$campos[ 'muestra' ] = [ 
		'title'			=> __( 'Show only APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( "Don't show others shipping cost.", 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> 'no',
];

return $campos;
