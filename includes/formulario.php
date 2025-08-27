<?php 
/**
 * Muestra el formulario de configuración del método de envío APG Free Shipping en el panel de administración de WooCommerce.
 *
 * Este archivo es cargado desde el método `admin_options()` de la clase de método de envío,
 * mostrando la descripción, cabecera con imagen y los campos de configuración generados automáticamente.
 *
 * Variables globales utilizadas:
 * @global array $apg_free_shipping  Información del plugin (nombre, url, etc.).
 * @var WC_apg_free_shipping $this   Instancia del método de envío.
 *
 * @package WC-APG-Free-Shipping
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

global $apg_free_shipping;
?>
<h3> <a href="<?php echo esc_url( $apg_free_shipping['plugin_url'] ?? '' ); ?>" title="Art Project Group"> <?php echo esc_html( $apg_free_shipping['plugin'] ?? '' ); ?> </a> </h3>
<p><?php echo wp_kses_post( $this->method_description ); ?></p>
<?php include( 'cuadro-informacion.php' ); ?>
<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image ?>
<div class="cabecera"> <a href="<?php echo esc_url( $apg_free_shipping['plugin_url'] ?? '' ); ?>" title="<?php echo esc_attr( $apg_free_shipping['plugin'] ?? '' ); ?>" target="_blank"> <img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_free_shipping ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_free_shipping['plugin'] ?? '' ); ?>" /> </a> </div>
<table class="form-table apg-table">
	<?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>
</table>
