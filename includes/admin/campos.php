<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array(
	'activo' => array(
		'title'						=> __( 'Enable/Disable', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'						=> 'checkbox',
		'label'						=> __( 'Enable this shipping method', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'					=> 'yes',
	),
	'title' => array( 
		'title' 					=> __( 'Method Title', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type' 						=> 'text',
		'description' 				=> __( 'This controls the title which the user sees during checkout.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'					=> $this->method_title,
		'desc_tip'					=> true,
	 ),
	'requires' => array( 
		'title' 					=> __( 'Free Shipping Requires...', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type' 						=> 'select',
		'class'						=> 'wc-enhanced-select',
		'options'					=> array( 
			''						=> __( 'N/A', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'cupon'					=> __( 'A valid free shipping coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'importe_minimo'		=> __( 'A minimum order amount (defined below)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'cualquiera'			=> __( 'A minimum order amount OR a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
			'ambos'					=> __( 'A minimum order amount AND a coupon', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		 ),
	 ),
	'importe_minimo' => array( 
				'title'				=> __( 'Minimum Order Amount', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'type'				=> 'price',
				'description' 		=> __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'default' 			=> '0',
				'desc_tip'      	=> true,
				'placeholder'		=> wc_format_localized_price( 0 )
	 ),
);
if ( WC()->shipping->get_shipping_classes() ) {
	$campos['clases_excluidas'] = array( 
		'title'			=> __( 'No shipping (Shipping class)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'desc_tip' 		=> sprintf( __( "Select the shipping class where %s doesn't accept free shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
		'css'			=> 'width: 450px;',
		'default'		=> '',
		'type'			=> 'multiselect',
		'class'			=> 'wc-enhanced-select',
		'options' 		=> array( 'todas' => __( 'All enabled shipping class', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ) + $this->clases_de_envio,
	);
}
$campos['roles_excluidos'] = array( 
	'title'			=> __( 'No shipping (User role)', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
	'desc_tip' 		=> sprintf( __( "Select the user role where %s doesn't accept free shippings.", 'woocommerce-apg-free-postcodestatecountry-shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> array( 
		'invitado' => __( 'Guest', 'woocommerce-apg-free-postcodestatecountry-shipping' ) 
	) + $this->roles_de_usuario,
);
$campos['pago'] = array(
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
$campos['icono'] = array( 
		'title'			=> __( 'Icon image', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_free_shipping ),
		'desc_tip'		=> true,
);
$campos['muestra_icono'] = array( 
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
$campos['entrega'] = array( 
		'title'			=> __( 'Estimated delivery time', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
);
$campos['muestra'] = array( 
		'title'			=> __( 'Show only APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( "Don't show others shipping cost.", 'woocommerce-apg-free-postcodestatecountry-shipping' ),
		'default'		=> 'no',
);

return $campos;
?>
