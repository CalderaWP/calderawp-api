<?php
/**
 * Endpoints for products.
 *
 * @package   calderawp\calderawp_api\
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\calderawp_api\routes;


class products extends endpoints {


	/**
	 * Get a single product
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_item( $request) {
		$params = $request->get_params();
		$id = $params[ 'id' ];
		if ( 1 < $id ) {
			$post = get_post( $id );
		}else{
			$post = null;
		}


		if ( $post ) {
			$data = $this->make_data( $post, array() );

			$response = rest_ensure_response( $data );
			$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );
		}else{
			$response = new \WP_REST_Response( 0, 404, array() );
		}

		$response->set_matched_route( $request->get_route() );

		return $response;

	}

	/**
	 * Get multiple products
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();

		if ( $params[ 'slug' ] ) {
			$args[ 'name' ] = $params[ 'slug' ];
			$args[ 'post_type' ] = $this->post_type;
		}elseif( $params[ 'soon' ] ) {
			$args[ 'meta_key' ] = 'edd_coming_soon';
			$args[ 'meta_value' ] = true;
		}else{
			$args = $this->query_args( $params );
		}


		return $this->do_query( $request, $args );

	}

	/**
	 * Get Caldera Forms add-ons
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_cf_addons( $request ) {
		$params = $request->get_params();
		$args = $this->query_args( $params );
		$args[ 'tax_query' ] = array(
			array(
				'taxonomy' => 'download_category',
				'field'    => 'slug',
				'terms'    => 'all-cf-addons',
			),
		);

		return $this->do_query( $request, $args );

	}

	/**
	 * Get featured plugins
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_featured( $request ) {
		$params = $request->get_params();
		$args = $this->query_args( $params );
		$args[ 'meta_key' ] = 'show_on_front_page';
		$args[ 'meta_value' ] = true;

		return $this->do_query( $request, $args );

	}

	/**
	 * Add current post to response data for this route.
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_Post $post Current post object.
	 * @param array $data Current collection of data
	 *
	 * @return array
	 */
	protected function make_data( $post, $data ) {
		$image = get_post_thumbnail_id( $post->ID );
		if ( $image ) {
			$_image = wp_get_attachment_image_src( $image, 'large' );
			if ( is_array( $_image ) ) {
				$image = $_image[0];
			}

		}

		$data[ $post->ID ] = array(
			'name'         => $post->post_title,
			'link'         => get_the_permalink( $post->ID ),
			'image_markup' => get_the_post_thumbnail( $post->ID, 'large' ),
			'image_src'    => $image,
			'excerpt'      => $post->post_excerpt,
			'tagline'      => get_post_meta( $post->ID, 'tagline', true ),
			'prices'       => edd_get_variable_prices( $post->ID ),
			'slug'         => $post->post_name,
		);

		for ( $i = 1; $i <= 3; $i++ ) {
			foreach( array(
				'title',
				'text',
				'image'
			) as $field ) {
				$field = "benefit_{$i}_{$field}";
				$data[ $field ] = get_post_meta( $post->ID, $field, true );
			}

		}


		return $data;

	}



}
