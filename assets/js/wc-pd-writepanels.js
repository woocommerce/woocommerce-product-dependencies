jQuery( function( $ ) {

	var $product_dependencies_dropdown     = $( '#product_dependencies_dropdown' ),
		$product_ids_dependencies_choice   = $( '#product_ids_dependencies_choice' ),
		$category_ids_dependencies_choice  = $( '#category_ids_dependencies_choice' );

	function initialize() {
		if ( $product_dependencies_dropdown.val() == 'product_ids' ) {
			$product_ids_dependencies_choice.show();
			$category_ids_dependencies_choice.hide();

		} else {
			$category_ids_dependencies_choice.show();
			$product_ids_dependencies_choice.hide();
		}
	}

	initialize();

	$product_dependencies_dropdown.change( function() {
		if ( $product_dependencies_dropdown.val() == 'product_ids' ) {
			$product_ids_dependencies_choice.show();
			$category_ids_dependencies_choice.hide();

		} else {
			$category_ids_dependencies_choice.show();
			$product_ids_dependencies_choice.hide();

		}
	} );
} );