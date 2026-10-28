<?php
/**
 * Ayuda contextual de los ajustes del método de envío: la pestaña «Ayuda» nativa de WordPress, con
 * un panel por cada bloque de la pantalla y una entrada por cada ajuste. La versión completa vive en
 * la web y se enlaza desde el lateral.
 *
 * A diferencia de un plugin con su propia página de ajustes, aquí la pantalla es la de ajustes de
 * WooCommerce, compartida con todo lo demás: los paneles sólo se añaden cuando se está editando una
 * instancia de este método, y la ayuda de WooCommerce se respeta en lugar de sustituirla.
 *
 * @package WC-APG-Free-Shipping
 */

defined( 'ABSPATH' ) || exit;

/**
 * Añade los paneles de ayuda a los ajustes del método de envío.
 */
class APG_Free_Shipping_Help {

	/**
	 * Identificador del método de envío, para no añadir la ayuda en los ajustes de otro.
	 */
	const METODO = 'apg_free_shipping';

	/**
	 * Dirección de la documentación completa, en español.
	 */
	const DOCS_URL = 'https://artprojectgroup.es/documentacion/wc-apg-envio-gratuito';

	/**
	 * Dirección de la documentación completa, en inglés.
	 */
	const DOCS_URL_EN = 'https://artprojectgroup.com/documentation/wc-apg-envio-gratuito';

	/**
	 * Añade los paneles del plugin a la pantalla de ajustes de envío.
	 *
	 * @return void
	 */
	public static function add_tabs() {
		if ( ! self::es_pantalla_del_metodo() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Aquí no se llama a remove_help_tabs(): la pantalla es de WooCommerce y su ayuda sigue
		// siendo útil. Los paneles de este método se añaden detrás de los suyos.
		foreach ( self::get_panels() as $id => $panel ) {
			$screen->add_help_tab(
				array(
					'id'      => 'apg_free_shipping_help_' . $id,
					'title'   => $panel['title'],
					'content' => self::render( $panel['items'] ),
				)
			);
		}

		// El lateral también es de WooCommerce: se conserva lo que haya y se añade lo del plugin.
		$screen->set_help_sidebar(
			$screen->get_help_sidebar() .
			'<p><strong>' . esc_html__( 'More help', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</strong></p>' .
			'<p><a href="' . esc_url( self::documentacion() ) . '" target="_blank">' . esc_html__( 'Full documentation', 'woocommerce-apg-free-postcodestatecountry-shipping' ) . '</a></p>'
		);
	}

	/**
	 * Comprueba que se está editando una instancia de este método de envío.
	 *
	 * Los parámetros se leen de la dirección sin nonce porque sólo deciden si se muestra la ayuda:
	 * no cambian nada ni dan acceso a nada.
	 *
	 * @return bool
	 */
	private static function es_pantalla_del_metodo() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$pestana   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		$instancia = isset( $_GET['instance_id'] ) ? absint( wp_unslash( $_GET['instance_id'] ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( 'shipping' !== $pestana || ! $instancia || ! class_exists( 'WC_Shipping_Zones' ) ) {
			return false;
		}

		$metodo = WC_Shipping_Zones::get_shipping_method( $instancia );

		return ( $metodo && self::METODO === $metodo->id );
	}

	/**
	 * Dirección de la documentación en el idioma de quien lee la pantalla.
	 *
	 * @return string
	 */
	private static function documentacion() {
		return ( 0 === strpos( get_user_locale(), 'es' ) ) ? self::DOCS_URL : self::DOCS_URL_EN;
	}

	/**
	 * Pinta un panel: un párrafo de entrada y una lista de término => explicación.
	 *
	 * @param array $items Primer elemento: texto de entrada (cadena). Después, término => explicación.
	 * @return string
	 */
	private static function render( array $items ) {
		$html = '';
		$list = '';

		foreach ( $items as $term => $text ) {
			if ( is_int( $term ) ) {
				$html .= '<p>' . esc_html( $text ) . '</p>';
			} else {
				$list .= '<li><strong>' . esc_html( $term ) . '</strong>: ' . esc_html( $text ) . '</li>';
			}
		}

		return $html . ( '' !== $list ? '<ul>' . $list . '</ul>' : '' );
	}

	/**
	 * Paneles de la pantalla de ajustes, en el orden de sus bloques.
	 *
	 * @return array ID del panel => array( title, items ).
	 */
	private static function get_panels() {
		return array(
			'method'     => array(
				'title' => __( 'The method', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'This method belongs to the shipping zone you are editing, so free shipping is only ever offered to the countries, states and postcodes of that zone. You can add it as many times as you need, each copy with its own conditions.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Method Title', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'the name the customer reads at checkout. APG Free Shipping by default.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				),
			),

			'conditions' => array(
				'title' => __( 'When shipping is free', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What the cart has to meet before this method shows up. With nothing set, shipping is free for everyone in the zone.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Free Shipping Requires...', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'the condition: nothing, a valid free shipping coupon, a minimum order amount, either of the two, or both at once.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Minimum Order Amount', 'woocommerce-apg-free-postcodestatecountry-shipping' )      => __( 'how much the customer has to spend. It only counts when the condition above asks for an amount.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Including taxes', 'woocommerce-apg-free-postcodestatecountry-shipping' )           => __( 'whether that amount is compared against the total with taxes or without them. Unticked, which is how it comes, taxes are left out.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'No shipping (Max. weight)', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'a weight above which shipping stops being free, however much the order costs. Leave it at zero to switch it off.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				),
			),

			'exclusions' => array(
				'title' => __( 'Exclusions', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What never travels free. There are five pairs of settings, one for product categories, tags, attributes, shipping classes and user roles, and the two in each pair work together.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'No shipping (...)', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'the list of categories, tags, attributes, shipping classes or roles that do not get free shipping. Leave a list empty and nothing is excluded by it. With more than five hundred entries the list turns into a search box, so you type instead of scrolling.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Shipping (...)?', 'woocommerce-apg-free-postcodestatecountry-shipping' )   => __( 'turns the list around: ticked, free shipping is offered only for what is on the list and for nothing else.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				),
			),

			'checkout'   => array(
				'title' => __( 'At checkout', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'Which payment methods and which other shipping methods can be used together with this one.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Payment gateway', 'woocommerce-apg-free-postcodestatecountry-shipping' )          => __( 'the payment methods the customer is offered when choosing free shipping. Useful for leaving cash on delivery out of an order that travels free.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Shipping methods', 'woocommerce-apg-free-postcodestatecountry-shipping' )         => __( 'the other shipping methods of the same zone that are shown alongside this one.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Show only APG Free Shipping', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'hides every other shipping cost whenever free shipping is available, so the customer cannot pay for something that is already free.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				),
			),

			'appearance' => array(
				'title' => __( 'How it looks', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				'items' => array(
					__( 'What the customer sees next to the method at checkout.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Icon image', 'woocommerce-apg-free-postcodestatecountry-shipping' )                  => __( 'the address of an image to show with the method, typically the carrier logo. 60x21 pixels works well.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'How to show the icon image?', 'woocommerce-apg-free-postcodestatecountry-shipping' ) => __( 'where the icon goes: before the title, after it, or on its own with no title at all.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Estimated delivery time', 'woocommerce-apg-free-postcodestatecountry-shipping' )     => __( 'a free text shown under the method, such as 24 to 48 hours. Leave it empty and nothing is shown.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
					__( 'Show price', 'woocommerce-apg-free-postcodestatecountry-shipping' )                  => __( 'writes the zero price after the title instead of leaving the method with no figure, which some customers read as an error.', 'woocommerce-apg-free-postcodestatecountry-shipping' ),
				),
			),
		);
	}
}
