'use strict'

/**
 * Primary Taxonomy Term selection script.
 *
 * This script will append a term selection dropdown to the metaboxes for
 * taxonomies configured to allow primary term selection.
 *
 * Original idea from: https://github.com/asharirfan/simple-primary-category
 *
 * @package surrealwebs-primary-taxonomy
 */
jQuery( document ).ready( function () {
	let taxonomies = localizedPrimaryTaxonomyData.taxonomies;
	let tplSelectPrimaryTerm = wp.template( 'tpl-select-primary-term' );

	/**
	 * Go through each post taxonomy and add relevant
	 * primary taxonomy selector and handlers to each box.
	 */
	jQuery( _.values( taxonomies ) ).each( function ( index, taxonomy ) {
		let taxonomyMetabox = jQuery( '#taxonomy-'.concat( taxonomy.name ) );
		let primaryTermInputHtml = tplSelectPrimaryTerm( {
			taxonomy: taxonomy
		} );

		taxonomyMetabox.append( primaryTermInputHtml );

		updatePrimaryTermSelector( taxonomy.name );

		taxonomyMetabox.on(
			'click',
			'input[type="checkbox"]',
			handleUpdateTerm( taxonomy.name )
		);

		taxonomyMetabox.on(
			'wpListAddEnd',
			'#'.concat( taxonomy.name, 'checklist' ),
			handleListUpdate( taxonomy.name )
		);
	} );

	/**
	 * Update Primary Term Selector on load.
	 *
	 * @param {string} taxonomy Taxonomy name.
	 */
	function updatePrimaryTermSelector( taxonomy ) {
		let checkedItems = jQuery(
			'#'.concat(
				taxonomy,
				'checklist input[type="checkbox"]'
			)
		);

		if ( 1 > checkedItems.length ) {
			return;
		}

		checkedItems.each( function ( index, term ) {
			term = jQuery( term );

			if ( ! term.is( ':checked' ) ) {
				removePrimarySelectOption( taxonomy, term.val() );
			}
		} );
	}

	/**
	 * Update Primary Taxonomy selector when terms are un/checked.
	 *
	 * @param {string} taxonomy Taxonomy name.
	 */
	function handleUpdateTerm( taxonomy ) {
		return function () {
			if ( jQuery( this ).is( ':checked' ) ) {
				addPrimarySelectOption(
					taxonomy,
					jQuery( this ).val(),
					jQuery( this ).parent().text()
				);
			} else {
				removePrimarySelectOption(
					taxonomy,
					jQuery( this ).val()
				);
			}
		};
	}

	/**
	 * Update Primary Taxonomy selector when a new term is added.
	 *
	 * @param {string} taxonomy Taxonomy name.
	 */
	function handleListUpdate( taxonomy ) {
		return function () {
			let primaryTermInput = jQuery( '#surrealwebs-primary-term-'.concat( taxonomy ) );
			let checkedItems = jQuery(
				'#'.concat(
					taxonomy,
					'checklist input[type="checkbox"]:checked'
				)
			);

			if ( 1 > checkedItems.length ) {
				return;
			}

			checkedItems.each( function ( index, term ) {
				term = jQuery( term );

				if ( ! primaryTermInput.find( 'option[value='.concat( term.val(), ']' ) ).length ) {
					addPrimarySelectOption( taxonomy, term.val(), term.parent().text() );
				}
			} );
		};
	}

	/**
	 * Add option to Primary Taxonomy selector.
	 *
	 * @param {string} taxonomy Taxonomy name.
	 * @param {string} value Term id.
	 * @param {string} text Term name.
	 */
	function addPrimarySelectOption( taxonomy, value, text ) {
		let primaryTermInput = jQuery( '#surrealwebs-primary-term-'.concat( taxonomy ) );
		let termOption = jQuery( '<option></option>' );
		termOption.prop( 'value', value );
		termOption.html( text.trim() );
		primaryTermInput.append( termOption );
	}

	/**
	 * Remove option from Primary Taxonomy selector.
	 *
	 * @param {string} taxonomy Taxonomy name.
	 * @param {string} value Term id.
	 */
	function removePrimarySelectOption( taxonomy, value ) {
		let primaryTermInput = jQuery( '#surrealwebs-primary-term-'.concat( taxonomy ) );
		primaryTermInput.find( 'option[value='.concat( value, ']' ) ).remove();
	}
} );
