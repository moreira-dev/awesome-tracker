jQuery( document ).ready( function() {

	jQuery( '.at_chosen_user' ).chosen( {
		disable_search_threshold: 0,
		inherit_select_classes: true,
		width: '100%',
		placeholder_text_single: ati18n.all_users,
		placeholder_text_multiple: ati18n.all_users,
		no_results_text: ati18n.no_users,
		max_selected_options: 1,
	} );
	jQuery( 'select.at_chosen_user' ).change( function() {
		jQuery( 'select[name="at_users_filter"]' ).val( jQuery( this ).val() ).trigger( 'chosen:updated' );
		jQuery( this ).closest( 'form' ).submit();
	} );

} );

