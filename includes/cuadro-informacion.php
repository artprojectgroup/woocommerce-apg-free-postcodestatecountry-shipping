<div class="informacion">
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
	<div class="fila">
		<div class="columna">
			<p>
				<?php esc_html_e( 'Follow us:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://www.facebook.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>Facebook" target="_blank"><span class="genericon genericon-facebook-alt"></span></a> <a href="https://twitter.com/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>Twitter" target="_blank"><span class="genericon genericon-twitter"></span></a> <a href="https://es.linkedin.com/in/artprojectgroup" title="<?php echo esc_attr__( 'Follow us on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>LinkedIn" target="_blank"><span class="genericon genericon-linkedin"></span></a> </p>
		</div>
		<div class="columna">
			<p>
				<?php esc_html_e( 'More plugins:', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>
			</p>
			<p> <a href="https://profiles.wordpress.org/artprojectgroup/" title="<?php echo esc_attr__( 'More plugins on ', 'woocommerce-apg-free-postcodestatecountry-shipping' ); ?>WordPress" target="_blank"><span class="genericon genericon-wordpress"></span></a> </p>
		</div>
	</div>
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
