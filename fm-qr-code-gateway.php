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
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'plugins_loaded', 'fm_qr_code_gateway_init', 11 );
/**
 * Initialize the Classic FM QR Code WC Payment Gateway
*/
function fm_qr_code_gateway_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
    add_filter( 'woocommerce_payment_gateways', function($methods){
        $methods[] = 'FM_QR_Code_Gateway_WC';
        return $methods;
    });

    class FM_QR_Code_Gateway_WC extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                   = 'fm-qr-code-gateway';
            $this->method_title         = __( 'FM QR Code Gateway for WooCommerce', 'fm-qr-code-gateway' );
            $this->method_description   = __( 'Display a QR code at checkout. Customer must enter transaction ID.', 'fm-qr-code-gateway' );
            $this->has_fields           = true;

            $this->init_form_fields();
            $this->init_settings();

            $this->title        = $this->get_option( 'title', 'QR Code Payment' );
            $this->description  = $this->get_option( 'description', '' );
            $this->qr_url       = $this->get_option( 'qr_url', '' );
            $this->icon         = plugin_dir_url(__FILE__) . 'assets/images/icon.png';
            // Admin settings save
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        /**
         * Initialize the Form Fields
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'fm-qr-code-gateway' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable QR Code Gateway', 'fm-qr-code-gateway' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'         => __( 'Title', 'fm-qr-code-gateway' ),
                    'type'          => 'text',
                    'description'   => __( 'Title shown to customers at checkout', 'fm-qr-code-gateway' ),
                    'default'       => __( 'QR Payment', 'fm-qr-code-gateway' )
                ),
                'description' => array(
                    'title'         => __( 'Description', 'fm-qr-code-gateway' ),
                    'type'          => 'textarea',
                    'description'   => __( 'Description shown to customers at checkout', 'fm-qr-code-gateway' ),
                    'default'       => __( 'Scan the QR code and enter transaction ID to complete order.', 'fm-qr-code-gateway' )
                ),
                'qr_url' => array(
                    'title'         => __( 'QR Code URL', 'fm-qr-code-gateway' ),
                    'type'          => 'text',
                    'description'   => __( 'Enter the URL of the QR code to display at checkout.', 'fm-qr-code-gateway' ),
                    'default'       => ''
                ),
            );
        }

        /**
         * Classic checkout fields
         */
        public function payment_fields() {
            echo '<p>' . esc_html( $this->description ) . '</p>';

            if ( ! empty( $this->qr_url ) ) {
                echo '<div style="margin:10px 0;"><img src="' . esc_url( $this->qr_url ) . '" alt="QR Code" style="max-width:200px;"></div>';
            }

            ?>
            <p>
                <label for="fm_qr_transaction_id"><?php esc_html_e( 'Transaction ID', 'fm-qr-code-gateway' ); ?> <span class="required">*</span></label><br/>
                <input type="text" id="fm_qr_transaction_id" name="fm_qr_transaction_id" value="" placeholder="<?php esc_attr_e( 'Transaction ID/ UPI Reference', 'fm-qr-code-gateway' ); ?>" style=" width:80%; padding:8px; margin-top:4px;" required />
            </p>
            <?php
        }

        /**
         * Process payment for classic checkout
         */
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            // Stop process_payment for Block-based checkout
            if ( defined('REST_REQUEST') && REST_REQUEST && isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wc/store/v1/checkout') !== false ) {
                // Return early, do nothing
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            }

            // Classic checkout processing
            $transaction_id = sanitize_text_field( $_POST['fm_qr_transaction_id'] );
            $order->update_meta_data( 'fm_qr_code_transaction_id', $transaction_id );
            // Translators: %s is the Transaction ID entered by the customer.
            $order->add_order_note( sprintf( __( 'Transaction ID entered: %s', 'fm-qr-code-gateway' ), $transaction_id ) );
            $order->payment_complete();
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }
    }

    /**
     * Validate that the Transaction ID field is required
     */
    add_action( 'woocommerce_checkout_process', function() {
        if ( isset( $_POST['payment_method'] ) && $_POST['payment_method'] === 'fm-qr-code-gateway' ) {
            if ( empty( $_POST['fm_qr_transaction_id'] ) ) {
                wc_add_notice( __( 'Please enter your QR Transaction ID to complete the order.', 'fm-qr-code-gateway' ), 'error' );
            }
        }
    });
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature 
*/
function fm_qr_code_cart_checkout_blocks_compatibility() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
add_action('before_woocommerce_init', 'fm_qr_code_cart_checkout_blocks_compatibility');
add_action( 'woocommerce_blocks_loaded', 'fm_qr_code_payment_method_type_for_blocks' );

/**
 * Function to Register the FM QR code as the Block Payment Support
 */
function fm_qr_code_payment_method_type_for_blocks() {
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }
    require_once plugin_dir_path(__FILE__) . 'fm-qr-class-block.php';
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register( new FM_QR_Code_Gateway_Blocks );
        }
    );
}

/**
 * Store the Transaction ID after successfully payment complete for Block Payment
 */
add_action( 'woocommerce_rest_checkout_process_payment_with_context', function( $context, $result ) {
    if ( $context->payment_method === 'fm-qr-code-gateway' ) {

        // Correct key from JS
        $transaction_id = $context->payment_data['fm_qr_transaction_id'] ?? '';

        if ( ! empty( $transaction_id ) ) {
            $order = $context->order;
            // Translators: %s is the Transaction ID entered by the customer.
            $order->add_order_note( sprintf(__( 'Transaction ID / UPI Reference: %s', 'fm-qr-code-gateway' ),esc_html( $transaction_id )), true );
            $order->update_meta_data( 'fm_qr_code_transaction_id', wc_clean( $transaction_id ) );
            $order->payment_complete();
            $order->save();
        } else {
            // Add a safety error if empty
            $result->set_status( 'failed' );
            $result->set_message( __( 'Transaction ID missing. Please try again.', 'fm-qr-code-gateway' ) );
        }
    }
}, 10, 2);