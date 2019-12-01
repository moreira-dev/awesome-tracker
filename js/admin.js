jQuery( document ).ready( function() {

	const selectTypes = [ 'users', 'countries' ];

	selectTypes.forEach( function( type ) {
		jQuery( '.at_chosen_' + type ).chosen( {
			disable_search_threshold: 0,
			inherit_select_classes: true,
			width: '100%',
			placeholder_text_single: ati18n[ 'all_' + type ],
			placeholder_text_multiple: ati18n[ 'all_' + type ],
			no_results_text: ati18n[ 'no_' + type ],
			max_selected_options: 1,
		} );
		jQuery( 'select.at_chosen_' + type ).change( function() {
			jQuery( 'select[name="at_' + type + '_filter"]' ).val( jQuery( this ).val() ).trigger( 'chosen:updated' );
			jQuery( this ).closest( 'form' ).submit();
		} );
	} );

} );

