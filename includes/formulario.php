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
<?php include( __DIR__ . '/cuadro-informacion.php' ); ?>
<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin image ?>
<div class="cabecera"> <a href="<?php echo esc_url( $apg_free_shipping['plugin_url'] ?? '' ); ?>" title="<?php echo esc_attr( $apg_free_shipping['plugin'] ?? '' ); ?>" target="_blank"> <img src="<?php echo esc_url( plugins_url( 'assets/images/cabecera.jpg', DIRECCION_apg_free_shipping ) ); ?>" class="imagen" alt="<?php echo esc_attr( $apg_free_shipping['plugin'] ?? '' ); ?>" /> </a> </div>
<table class="form-table apg-table">
	<?php $this->generate_settings_html( $this->get_instance_form_fields() ); ?>

</table>
<script>
jQuery(function($){
    function initAjaxSelect($el){
        if(!$el.length || !$el.is("select")) return;
        var data = $el.data();
        if(!data.apgAjax) return;
        var src = data.source || "";
        var nonce = data.nonce || "";
        $el.selectWoo({
            ajax: {
                transport: function(params, success, failure){
                    $.ajax({
                        url: ajaxurl,
                        method: "GET",
                        data: {
                            action: "apg_free_shipping_search_terms",
                            source: src,
                            nonce: nonce,
                            q: params.data.q || "",
                            page: params.data.page || 1
                        }
                    }).then(success).catch(failure);
                },
                delay: 250,
                data: function(params){ return { q: params.term || "", page: params.page || 1 }; },
                processResults: function(data){ return data || { results: [] }; }
            },
            minimumInputLength: 1,
            allowClear: true,
            placeholder: "<?php echo esc_js( __( 'Search…', 'woocommerce-apg-free-postcodestatecountry-shipping' ) ); ?>"
        });
    }
    $("select.apg-ajax-select").each(function(){ initAjaxSelect($(this)); });
    $(document.body).on("wc-enhanced-select-init", function(){
        $("select.apg-ajax-select").each(function(){
            if(!$(this).data("select2")) initAjaxSelect($(this));
        });
    });
});
</script>
