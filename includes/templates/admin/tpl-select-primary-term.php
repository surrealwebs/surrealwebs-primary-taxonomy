<?php
/**
 * JS Template used to create primary taxonomy select boxes.
 *
 * @package surrealwebs-primary-taxonomy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<style>
	.surrealwebs-primary-term select { width: 100% }
</style>

<script type="text/html" id="tmpl-tpl-select-primary-term">
	<div class="surrealwebs-primary-term">
		<p class="surrealwebs-primary-term-heading"><strong>Primary {{data.taxonomy.title}}</strong></p>
		<select
				id="surrealwebs-primary-term-{{data.taxonomy.name}}"
				name="<?php echo esc_attr( SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE ); ?>_{{data.taxonomy.name}}"
		>
			<option value="-1">— Select Primary {{data.taxonomy.title}} —</option>
			<# _( data.taxonomy.terms ).each( function( term ) { #>
				<option value="{{term.id}}"
				<# if ( data.taxonomy.primary === term.id ) { #>
					selected
				<# } #>
				>{{term.name}}</option>
			<# }); #>
		</select>
		<?php
		wp_nonce_field(
			'sw_primary_tax_save',
			sprintf(
				'%s_%s_%s',
				SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE,
				'{{data.taxonomy.name}}',
				'nonce'
			)
		);
		?>
	</div>
</script>
