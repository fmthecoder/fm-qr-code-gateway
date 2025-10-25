<?php
/**
 * File Description
 *
 * This file is responsible for the Block based Payment Method.
 *
 * @package    fmthecoder
 *
 * @since      1.0.0
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
/**
 * FM_QR_Code_Gateway_Blocks
 */
final class FM_QR_Code_Gateway_Blocks extends AbstractPaymentMethodType {
	/**
	 * Gateway
	 *
	 * @var mixed
	 */
	private $gateway;
	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = 'fm-qr-code-gateway';
	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_fm-qr-code-gateway_settings', array() );
		$this->gateway  = new FM_QR_Code_Gateway_WC();
	}

	/**
	 * Is_active
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Get_payment_method_script_handles
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		wp_register_script(
			'fm-qr-code-gateway-blocks-integration',
			plugin_dir_url( __FILE__ ) . 'assets/js/fm-qr-checkout.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
			),
			'1.0.0',
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'fm-qr-code-gateway-blocks-integration' );
		}

		return array( 'fm-qr-code-gateway-blocks-integration' );
	}

	/**
	 * Get_payment_method_data
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title'       => $this->gateway->title,
			'description' => $this->gateway->description,
			'qr_url'      => $this->gateway->qr_url,
			'meta'        => array(
				'fm_qr_transaction_id' => '',
			),
		);
	}
}
