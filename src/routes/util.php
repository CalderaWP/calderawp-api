<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2015 Josh Pollock
 */

namespace calderawp\calderawp_api\routes;


class util {

	/**
	 * Get utility stuff
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();
		$what = $params[ 'what' ];
		switch ( $what ) {
			case 'mailchimp' == $what :
				$data[ 'mailchimp' ] = $this->mailchimp();
				break;
			case 'support' == $what :
				$data[ 'support' ] = $this->support();
				break;
			default :
				$data[ 'mailchimp' ] = $this->mailchimp();
				$data[ 'support' ] = $this->support();
				break;
		}

		$response    = rest_ensure_response( $data );

		return $response;

	}

	/**
	 * Mailchimp subscribe form.
	 *
	 * @since 0.0.1
	 *
	 * @return string
	 */
	protected function mailchimp() {
		$data[ 'title' ] = __( 'Join Our Mailing List', 'calderawp-license-manager' );
		$data[ 'message' ] = __( 'Get news, WordPress tricks and special saving.', 'calderawp-license-manager' );
		$data[ 'form' ] =  include dirname( __FILE__ ) . '/include/mailchimp.php';

		return $data;

	}

	/**
	 * Support form.
	 *
	 * @todo this
	 *
	 * @since 0.0.1
	 *
	 * @return string
	 */
	protected function support() {
		$data[ 'title' ] = '';
		$data[ 'message' ] = '';
		$data[ 'form' ] = include dirname( __FILE__ ) . '/include/support-form.php';

		return $data;

	}

}
