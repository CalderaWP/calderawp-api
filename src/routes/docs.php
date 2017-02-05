<?php
/**
 * Docs endpoints
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\calderawp_api\routes;


class docs extends endpoints {

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
	 * Get docs by product ID, product slug, or all docs.
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_HTTP_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();
		$args = $this->query_args( $params );

		if ( $params[ 'product_id' ] ) {
			$args[ 'meta_key' ] = 'product';
			$args[ 'meta_value' ] = $params[ 'product_id' ];
		}elseif( 'false' !== $params[ 'product_slug' ]  ) {
			$product = $this->find_product( $params[ 'product_slug' ] );
			if ( is_object( $product ) ) {
				$args[ 'meta_key' ] = 'product';
				$args[ 'meta_value' ] = $product->ID;
			}else{
				return new \WP_Error( 'calderawp-api-invalid-product-slug' );
			}
		}elseif('false' !==  $params[ 'doc_slug' ] ) {
			$args[ 'name' ] = $params[ 'doc_slug' ];
		}

		return $this->do_query( $request, $args );

	}

	/**
	 * Add current post to response data for this route.
	 *
	 * @since 0.0.1
	 *
	 * @access protected
	 *
	 * @param \WP_Post $post Current post object.
	 * @param array $data Current collection of data
	 * @param int|string||WP_Post $product Product this is a doc for. If null, the default, it will be queried for by slug or ID.
	 *
	 * @return array
	 */
	protected function make_data( $post, $data, $product = null ) {
		if ( ! is_object( $product ) ) {
			$product = $this->find_product( $product );
		}

		$data[ $post->ID ] = array(
			'title'        => $post->post_title,
			'link'         => get_the_permalink( $post->ID ),
			'excerpt'      => $post->post_excerpt,
			'content'       => $post->post_content,
			'product_name' => null,
			'product_link' => null,
			'slug'         => $post->post_name,
		);

		if ( ! is_null( $product ) ) {
			$data[ $post->ID ][ 'product_name' ] = $product->post_title;
			$data[ $post->ID ][ 'product_link' ] = get_the_permalink( $product->ID );
		}

		return $data;

	}

	/**
	 * Find product by ID or slug.
	 *
	 * @since 0.0.1
	 *
	 * @access protected
	 *
	 * @param int|string $id_or_slug
	 */
	protected function find_product( $id_or_slug ) {
		if ( is_int( $id_or_slug ) ) {
			$args[ 'meta_key' ] = 'product';
			$args[ 'meta_value' ] = $id_or_slug;
		}else{
			$args[ 'name' ] = $id_or_slug;
		}

		$args[ 'post_type' ] = 'download';

		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			return $query->posts[0];
		}


	}


    /**
     * @inheritdoc
     * @since 1.2.0
     */
	protected function query_args($params){
        $params =  parent::query_args($params);
        unset( $params[ 'meta_key' ] );
        unset( $params[ 'orderby' ] );
        return $params;

    }


}
