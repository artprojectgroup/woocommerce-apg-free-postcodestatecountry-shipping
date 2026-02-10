<?php 
/**
 * Muestra la información adicional y enlaces de Art Project Group en la pantalla de configuración del método de envío.
 *
 * Este archivo imprime enlaces a donaciones, redes sociales, otros plugins, contacto y soporte, 
 * además de invitar a valorar el plugin. 
 * 
 * Variables globales utilizadas:
 * @global array $apg_free_shipping  Información sobre el plugin (donación, soporte, URLs, etc.).
 *
 * @package WC-APG-Free-Shipping
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit; ?>
<div class="informacion">
	<!-- Fila: Donación y autor -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'If you enjoyed and find helpful this plugin, please make a donation:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="<?php echo esc_url( $apg_free_shipping['donacion'] ); ?>" target="_blank" title="<?php echo esc_attr__( 'Make a donation by ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>APG"> <span class="genericon genericon-cart"></span> </a> </p>
		</div>
		<div class="columna">
			<p>Art Project Group:</p>
			<p> <a href="https://www.artprojectgroup.es" title="Art Project Group" target="_blank"> <strong class="artprojectgroup">APG</strong> </a> </p>
		</div>
	</div>

	<!-- Fila: Redes sociales y más plugins -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Follow us:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://www.facebook.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://x.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>X" target="_blank"><span class="genericon genericon-x-alt"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'More plugins:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://profiles.wordpress.org/artprojectgroup/" title="<?php echo esc_attr__( 'More plugins on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a> </p>
		</div>
	</div>

	<!-- Fila: Contacto y Documentación/Soporte -->
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Contact with us:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="mailto:info@artprojectgroup.es" title="<?php echo esc_attr__( 'Contact with us by ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>e-mail"><span class="genericon genericon-mail"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'Documentation and Support:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="<?php echo esc_url( $apg_free_shipping['plugin_url'] ); ?>" title="<?php echo esc_attr( $apg_free_shipping['plugin'] ); ?>"><span class="genericon genericon-book"></span></a> <a href="<?php echo esc_url( $apg_free_shipping['soporte'] ); ?>" title="<?php echo esc_attr__( 'Support', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>"><span class="genericon genericon-cog"></span></a> </p>
		</div>
	</div>

	<!-- Fila final: Valoración -->
	<div class="fila final">
		<div class="columna">
			<p>
				<?php
				// translators: %s is the plugin name.
				echo esc_html( sprintf( __( 'Please, rate %s:', 'woocommerce-apg-free-postcodestatecountry-shipping' ), $apg_free_shipping[ 'plugin' ] ) );
				?>
			</p>
			<?php echo wp_kses_post( apg_free_shipping_plugin( $apg_free_shipping['plugin_uri'] ) ); ?> </div>
		<div class="columna final"></div>
	</div>
</div>
