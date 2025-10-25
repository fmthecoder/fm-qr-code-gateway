<?php
/**
 * Plugin Name:       FM: QR Code Gateway for WooCommerce
 * Description:       Accept UPI payments using any QR code in WooCommerce. Displays a QR code at checkout (supports Classic & Block Checkout). Customers must enter their UPI Transaction ID before placing the order, and the ID is saved with the order details.
 * Version:           1.0.0
 * Author:            Faiq Masood
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fm-qr-code-gateway
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Tested up to:      6.8
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 6.0
 * WC tested up to:   10.3.0
 *
 * @package fmthecoder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'fm_qr_code_gateway_init', 11 );
/**
 * Initialize the Classic FM QR Code WC Payment Gateway
 */
function fm_qr_code_gateway_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	add_filter(
		'woocommerce_payment_gateways',
		function ( $methods ) {
			$methods[] = 'FM_QR_Code_Gateway_WC';
			return $methods;
		}
	);

	include 'class-fm-qr-code-gateway-wc.php';

	/**
	 * Validate that the Transaction ID field is required
	 */
	add_action(
		'woocommerce_checkout_process',
		function () {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( isset( $_POST['payment_method'] ) && 'fm-qr-code-gateway' === $_POST['payment_method'] ) {
				if ( empty( $_POST['fm_qr_transaction_id'] ) ) {
					wc_add_notice( __( 'Please enter your QR Transaction ID to complete the order.', 'fm-qr-code-gateway' ), 'error' );
				}
			}
		}
	);
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature
 */
function fm_qr_code_cart_checkout_blocks_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'fm_qr_code_cart_checkout_blocks_compatibility' );
add_action( 'woocommerce_blocks_loaded', 'fm_qr_code_payment_method_type_for_blocks' );

/**
 * Function to Register the FM QR code as the Block Payment Support
 */
function fm_qr_code_payment_method_type_for_blocks() {
	if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}
	require_once plugin_dir_path( __FILE__ ) . 'class-fm-qr-code-gateway-blocks.php';
	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
			$payment_method_registry->register( new FM_QR_Code_Gateway_Blocks() );
		}
	);
}

/**
 * Store the Transaction ID after successfully payment complete for Block Payment
 */
add_action(
	'woocommerce_rest_checkout_process_payment_with_context',
	function ( $context, $result ) {
		if ( 'fm-qr-code-gateway' === $context->payment_method ) {
			$transaction_id = $context->payment_data['fm_qr_transaction_id'] ?? '';
			if ( ! empty( $transaction_id ) ) {
				$order = $context->order;
				// Translators: %s is the Transaction ID entered by the customer.
				$order->add_order_note( sprintf( __( 'Transaction ID / UPI Reference: %s', 'fm-qr-code-gateway' ), esc_html( $transaction_id ) ), true );
				$order->update_meta_data( 'fm_qr_code_transaction_id', wc_clean( $transaction_id ) );
				$order->payment_complete();
				$order->save();
			} else {
				// Add a safety error if empty.
				$result->set_status( 'failed' );
				$result->set_message( __( 'Transaction ID missing. Please try again.', 'fm-qr-code-gateway' ) );
			}
		}
	},
	10,
	2
);
