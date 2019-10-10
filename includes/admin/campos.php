<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array();
if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
	$campos[ 'activo' ] = array( 
		'title'			=> __( 'Enable/Disable', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( 'Enable this shipping method', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> 'yes'
	);
}
$campos[ 'title' ] = array( 
	'title' 				=> __( 'Method Title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type' 					=> 'text',
	'description' 			=> __( 'This controls the title which the user sees during checkout.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default'				=> $this->method_title,
	'desc_tip'				=> true,
 );
$campos[ 'requires' ] = array( 
	'title' 				=> __( 'Free Shipping Requires...', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type' 					=> 'select',
	'class'					=> 'wc-enhanced-select',
	'options'				=> array( 
		''					=> __( 'N/A', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'cupon'				=> __( 'A valid free shipping coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'importe_minimo'	=> __( 'A minimum order amount (defined below)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'cualquiera'		=> __( 'A minimum order amount OR a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'ambos'				=> __( 'A minimum order amount AND a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	 ),
 );
$campos[ 'importe_minimo' ] = array( 
	'title'				=> __( 'Minimum Order Amount', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type'				=> 'price',
	'description' 		=> __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default' 			=> '0',
	'desc_tip'      	=> true,
	'placeholder'		=> wc_format_localized_price( 0 )
);
$campos[ 'peso' ] = array( 
	'title'				=> __( 'No shipping (Max. weight)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'type'				=> 'text',
	'description' 		=> __( 'Users may not add more than this weight to get free shipping (if greater than zero).', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'default' 			=> '0',
	'desc_tip'      	=> true,
	'placeholder'		=> wc_format_localized_decimal( 0 ),
	'data_type' 		=> 'decimal',
	'class'				=> 'short wc_input_decimal'
);
$campos[ 'categorias_excluidas' ] = array( 
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product category', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Select the %s where %s doesn't accept shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product category', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> $this->categorias_de_producto,
);
$campos[ 'tipo_categorias' ] = array(
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product category', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product categories', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
);
$campos[ 'etiquetas_excluidas' ] = array( 
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Select the %s where %s doesn't accept shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> $this->etiquetas_de_producto,
);
$campos[ 'tipo_etiquetas' ] = array(
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Product tag', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'product tags', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
);

if ( WC()->shipping->get_shipping_classes() ) {
	$campos[ 'clases_excluidas' ] = array( 
		'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'desc_tip' 		=> sprintf( __( "Select the %s where %s doesn't accept free shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
		'css'			=> 'width: 450px;',
		'default'		=> '',
		'type'			=> 'multiselect',
		'class'			=> 'wc-enhanced-select',
		'options' 		=> array( 'todas' => __( 'All enabled shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ) + $this->clases_de_envio,
	);
	$campos[ 'tipo_clases' ] = array(
		'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'Shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'type'			=> 'checkbox',
		'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'shipping classes', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
		'default'		=> 'no',
	);
}
$campos[ 'roles_excluidos' ] = array( 
	'title'			=> sprintf( __( 'No shipping (%s)', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Select the %s where %s doesn't accept free shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'user role', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> array( 
		'invitado' => __( 'Guest', 'woocommerce-apg-free-postcodestatecountry-shipping' ) 
	) + $this->roles_de_usuario,
);
$campos[ 'tipo_roles' ] = array(
	'title'			=> sprintf( __( 'Shipping (%s)?', 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'User role', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'type'			=> 'checkbox',
	'label'			=> sprintf( __( "Ship only to the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'desc_tip' 		=> sprintf( __( "Check this field to accept shippings in the %s selected in the previous field.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), __( 'user roles', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ),
	'default'		=> 'no',
);
$campos[ 'pago' ] = array(
	'title'			=> __( 'Payment gateway', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'desc_tip'		=> sprintf( __( 'Payment gateway available for %s', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> array( 
		'todos' 
	),
	'type'			=> 'multiselect',
	'class'			=> 'chosen_select',
	'options' 		=> array( 
		'todos'		=> __( 'All enabled payments', 'woocommerce-apg-free-postcodestatecountry-shipping' )
	) + $this->metodos_de_pago,
);
$campos[ 'icono' ] = array( 
		'title'			=> __( 'Icon image', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_free_shipping ),
		'desc_tip'		=> true,
);
$campos[ 'muestra_icono' ] = array( 
		'title'			=> __( 'How show icon image?', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'desc_tip' 		=> __( 'Select how you want to show the icon image.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'no'			=> __( 'Not show, just title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'delante'		=> __( 'Before title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'detras'		=> __( 'After title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'solo'			=> __( 'No title, just icon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		 ),
);
$campos[ 'entrega' ] = array( 
		'title'			=> __( 'Estimated delivery time', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
);
$campos[ 'precio' ] = array( 
		'title'			=> sprintf( __( 'Show %s price', 'woocommerce-apg-free-postcodestatecountry-shipping' ), wc_price(0) ),
		'type'			=> 'checkbox',
		'label'			=> sprintf( __( "Show %s price after method title.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), wc_price(0) ),
		'default'		=> 'no',
);
$campos[ 'muestra' ] = array( 
		'title'			=> __( 'Show only APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( "Don't show others shipping cost.", 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> 'no',
);

return $campos;
