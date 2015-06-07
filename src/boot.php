<?php
/**
 * Boot the API and add our routes.
 *
 * @package   calderawp\calderawp_api
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\calderawp_api;


class boot {

	/**
	 * Constructor for class
	 *
	 *
	 * @since 0.0.1
	 *
	 * @param string $api_namespace
	 * @param $version
	 */
	public function __construct( $api_namespace, $version ) {
		$this->api_namespace = $api_namespace;
		$this->version = $version;
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register our endpoints
	 *
	 * @since 0.0.1
	 */
	public function register_routes() {
		$root = $this->api_namespace;
		$version = $this->version;

		/**
		 * Product endpoints
		 */
		$base = "{$root}/{$version}/products";
		$cb_class = new \calderawp\calderawp_api\routes\products( 'download', $base );

		/**
		 * Single product query
		 */
		register_rest_route( "{$root}/{$version}", '/products' . '/(?P<id>[\d]+)', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_item' ),
					'args'            => array(

					),
					'permission_callback' => array( $this, 'permissions_check' )
				),
			)
		);

		/**
		 * All products, or query products by slug.
		 */
		register_rest_route( "{$root}/{$version}", '/products', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_items' ),
					'args'            => array(
						'per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
						'soon' => array(
							'default' => 0,
							'sanitize_callback' => 'absint',
						),
						'slug' => array(
							'default' => false,
							'sanitize_callback' => 'sanitize_title',
						)

					),

					'permission_callback' => array( $this, 'permissions_check' )
				),
			)

		);

		/**
		 * Caldera Forms add-ons query
		 */
		register_rest_route( "{$root}/{$version}", '/products/cf-addons', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_cf_addons' ),
					'args'            => array(
						'per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
						'soon' => array(
							'default' => 0,
							'sanitize_callback' => 'absint',
						)

					),

					'permission_callback' => array( $this, 'permissions_check' )
				),
			)

		);

		/**
		 * Featured products
		 */
		register_rest_route( "{$root}/{$version}", '/products/featured', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_featured' ),
					'args'            => array(
						'per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
					),

					'permission_callback' => array( $this, 'permissions_check' )
				),
			)

		);

		/**
		 * Docs Endpoints
		 */
		$base = "{$root}/{$version}/docs";
		$cb_class = new \calderawp\calderawp_api\routes\docs( 'doc', $base );

		/**
		 * Single product documentation
		 */
		register_rest_route( "{$root}/{$version}", '/docs' . '/(?P<id>[\d]+)', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_item' ),
					'args'            => array(

					),
					'permission_callback' => array( $this, 'permissions_check' )
				),
			)
		);

		/**
		 * Docs for all products or a specific product by slug/ID
		 */
		register_rest_route( "{$root}/{$version}", '/docs', array(
				array(
					'methods'         => \WP_REST_Server::READABLE,
					'callback'        => array( $cb_class, 'get_items' ),
					'args'            => array(
						'per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
						'slug' => array(
							'default' => false,
							'sanitize_callback' => 'sanitize_title',
						),
						'product_slug' => array(
							'default' => false,
							'sanitize_callback' => 'sanitize_title',
						),
						'product_id' => array(
							'default' => 0,
							'sanitize_callback' => 'absint',
						),

					),

					'permission_callback' => array( $this, 'permissions_check' )
				),
			)

		);



	}

	/**
	 * For now, all methods are public.
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return bool Always returns true.
	 */
	public function permissions_check( $request ) {
		return true;

	}


}
