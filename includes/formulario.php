<?php global $apg_free_shipping; ?>

<h3><a href="<?php echo $apg_free_shipping['plugin_url']; ?>" title="Art Project Group"><?php echo $apg_free_shipping['plugin']; ?></a></h3>
<p>
  <?php _e( 'Lets you add a free shipping based on Postcode/State/Country of the cart and minimum order a amount and/or a valid free shipping coupon.', 'apg_free_shipping' ); ?>
</p>
<?php include( 'cuadro-informacion.php' ); ?>
<div class="cabecera"> <a href="<?php echo $apg_free_shipping['plugin_url']; ?>" title="<?php echo $apg_free_shipping['plugin']; ?>" target="_blank"><img src="<?php echo plugins_url( '../assets/images/cabecera.jpg', __FILE__ ); ?>" class="imagen" alt="<?php echo $apg_free_shipping['plugin']; ?>" /></a> </div>
<table class="form-table apg-table">
  <?php $this->generate_settings_html(); ?>
</table>
<!--/.form-table--> 
