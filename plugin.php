<?php
/**
Plugin Name: CalderaWP API
Plugin Version: 1.2.0
 */
add_action( 'plugins_loaded', function() {
	spl_autoload_register( function ( $class ) {
		$prefix = 'calderawp\\calderawp_api\\';
		$base_dir = dirname( __FILE__ ) . '/src/' ;
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0) {

			return;
		}
		$relative_class = substr($class, $len);
		$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

		if ( file_exists( $file )) {
			require $file;
		}
	});


	$api_namespace = 'calderawp_api';
	$version = 'v2';
	new \calderawp\calderawp_api\boot( $api_namespace, $version );


});

/**
 * For demo purposes, this a copy of the "doc" post type registered on site
 */
if( ! function_exists( 'caldera_theme_register_post_type' ) ){

    // Register Custom Post Type
    function caldera_theme_register_post_type() {

        $labels = array(
            'name'                  => 'Doc',
            'singular_name'         => 'Doc',
            'menu_name'             => 'Documentations',
            'name_admin_bar'        => 'Doc',
            'archives'              => 'Item Archives',
            'parent_item_colon'     => 'Parent Item:',
            'all_items'             => 'All Items',
            'add_new_item'          => 'Add New Item',
            'add_new'               => 'Add New',
            'new_item'              => 'New Item',
            'edit_item'             => 'Edit Item',
            'update_item'           => 'Update Item',
            'view_item'             => 'View Item',
            'search_items'          => 'Search Item',
            'not_found'             => 'Not found',
            'not_found_in_trash'    => 'Not found in Trash',
            'featured_image'        => 'Featured Image',
            'set_featured_image'    => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image'    => 'Use as featured image',
            'insert_into_item'      => 'Insert into item',
            'uploaded_to_this_item' => 'Uploaded to this item',
            'items_list'            => 'Items list',
            'items_list_navigation' => 'Items list navigation',
            'filter_items_list'     => 'Filter items list',
        );
        $args = array(
            'label'                 => 'Doc',
            'description'           => 'Post Type Description',
            'labels'                => $labels,
            'supports'              => array( 'thumbnail', 'excerpt', 'title', 'editor'),
            'taxonomies'            => array( 'category', 'post_tag' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );
        register_post_type( 'doc', $args );

    }

    add_action( 'init', 'caldera_theme_register_post_type', 0 );

}


