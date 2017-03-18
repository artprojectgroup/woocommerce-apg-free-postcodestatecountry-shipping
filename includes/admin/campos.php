<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Campos del formulario
$campos = array(
	'activo' => array(
		'title'						=> __( 'Enable/Disable', 'apg_free_shipping' ),
		'type'						=> 'checkbox',
		'label'						=> __( 'Enable this shipping method', 'apg_free_shipping' ),
		'default'					=> 'yes',
	),
	'title' => array( 
		'title' 					=> __( 'Method Title', 'apg_free_shipping' ),
		'type' 						=> 'text',
		'description' 				=> __( 'This controls the title which the user sees during checkout.', 'apg_free_shipping' ),
		'default'					=> $this->method_title,
		'desc_tip'					=> true,
	 ),
	'requires' => array( 
		'title' 					=> __( 'Free Shipping Requires...', 'apg_free_shipping' ),
		'type' 						=> 'select',
		'class'						=> 'wc-enhanced-select',
		'options'					=> array( 
			''						=> __( 'N/A', 'apg_free_shipping' ),
			'cupon'					=> __( 'A valid free shipping coupon', 'apg_free_shipping' ),
			'importe_minimo'		=> __( 'A minimum order amount (defined below)', 'apg_free_shipping' ),
			'cualquiera'			=> __( 'A minimum order amount OR a coupon', 'apg_free_shipping' ),
			'ambos'					=> __( 'A minimum order amount AND a coupon', 'apg_free_shipping' ),
		 ),
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
	$campos['clases_excluidas'] = array( 
		'title'			=> __( 'No shipping (Shipping class)', 'apg_free_shipping' ),
		'desc_tip' 		=> sprintf( __( "Select the shipping class where %s doesn't accept free shippings.", 'apg_free_shipping' ), $this->method_title ),
		'css'			=> 'width: 450px;',
		'default'		=> '',
		'type'			=> 'multiselect',
		'class'			=> 'wc-enhanced-select',
		'options' 		=> array( 'todas' => __( 'All enabled shipping class', 'apg_free_shipping' ) ) + $this->clases_de_envio,
	);
}
$campos['roles_excluidos'] = array( 
	'title'			=> __( 'No shipping (User role)', 'apg_free_shipping' ),
	'desc_tip' 		=> sprintf( __( "Select the user role where %s doesn't accept free shippings.", 'apg_free_shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> '',
	'type'			=> 'multiselect',
	'class'			=> 'wc-enhanced-select',
	'options' 		=> array( 
		'invitado' => __( 'Guest', 'apg_free_shipping' ) 
	) + $this->roles_de_usuario,
);
$campos['pago'] = array(
	'title'			=> __( 'Payment gateway', 'apg_free_shipping' ),
	'desc_tip'		=> sprintf( __( "Payment gateway available for %s", 'apg_free_shipping' ), $this->method_title ),
	'css'			=> 'width: 450px;',
	'default'		=> array( 
		'todos' 
	),
	'type'			=> 'multiselect',
	'class'			=> 'chosen_select',
	'options' 		=> array( 
		'todos'			=> __( 'All enabled payments', 'apg_free_shipping' )
	) + $this->metodos_de_pago,
);
$campos['icono'] = array( 
		'title'			=> __( 'Icon image', 'apg_free_shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Icon image URL. APG recommends a 60x21px image.', 'apg_free_shipping' ),
		'default'		=> plugins_url( 'assets/images/apg.jpg', DIRECCION_apg_free_shipping ),
		'desc_tip'		=> true,
);
$campos['muestra_icono'] = array( 
		'title'			=> __( 'How show icon image?', 'apg_free_shipping' ),
		'desc_tip' 		=> __( "Select how you want to show the icon image.", 'apg_free_shipping' ),
		'type'			=> 'select',
		'class'			=> 'wc-enhanced-select',
		'options'		=> array( 
			'no'			=> __( 'Not show, just title', 'apg_free_shipping' ),
			'delante'		=> __( 'Before title', 'apg_free_shipping' ),
			'detras'		=> __( 'After title', 'apg_free_shipping' ),
			'solo'			=> __( 'No title, just icon', 'apg_free_shipping' ),
		 ),
);
$campos['entrega'] = array( 
		'title'			=> __( 'Estimated delivery time', 'apg_free_shipping' ),
		'type'			=> 'text',
		'description'	=> __( 'Define estimation for delivery time for this shipping method.', 'apg_free_shipping' ),
		'default'		=> '',
		'desc_tip'		=> true,
);
$campos['muestra'] = array( 
		'title'			=> __( 'Show only APG Free Shipping', 'apg_free_shipping' ),
		'type'			=> 'checkbox',
		'label'			=> __( "Don't show others shipping cost.", 'apg_free_shipping' ),
		'default'		=> 'no',
);

return $campos;
?>
