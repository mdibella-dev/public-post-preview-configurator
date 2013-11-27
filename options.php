<?php
function ppp_configurator_show_settings_page() {
?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Public Post Preview Configurator</h2>
	<form method="post" action="options.php">
<?php 
	settings_fields('ppp_configurator_group');
	do_settings_sections('ppp_configurator_group');
?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Expiration hours</th>
					<td>
						<input id="ppp_configurator_expiration_hours" class="regular-text" type="text" value="<?php echo get_option('ppp_configurator_expiration_hours') ?>" name="ppp_configurator_expiration_hours" />
						<p class="description">Expiration of the preview link in hours, default = 48</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</div>

<?php
}
?>