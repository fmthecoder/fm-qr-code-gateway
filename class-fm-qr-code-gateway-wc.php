<?php
/**
 * File Description
 *
 * This file is responsible for extending the Classic Payment method.
 *
 * @package    fmthecoder
 *
 * @since      1.0.0
 */

/**
 * FM_QR_Code_Gateway_WC
 */
class FM_QR_Code_Gateway_WC extends WC_Payment_Gateway {
	/**
	 * Qr_url
	 *
	 * @var mixed
	 */
	public $qr_url;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'fm-qr-code-gateway';
		$this->method_title       = __( 'FM QR Code Gateway for WooCommerce', 'fm-qr-code-gateway' );
		$this->method_description = __( 'Display a QR code at checkout. Customer must enter transaction ID.', 'fm-qr-code-gateway' );
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title', 'QR Code Payment' );
		$this->description = $this->get_option( 'description', '' );
		$this->qr_url      = $this->get_option( 'qr_url', '' );
		$this->icon        = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		// Admin settings save.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize the Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'fm-qr-code-gateway' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable QR Code Gateway', 'fm-qr-code-gateway' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Title', 'fm-qr-code-gateway' ),
				'type'        => 'text',
				'description' => __( 'Title shown to customers at checkout', 'fm-qr-code-gateway' ),
				'default'     => __( 'QR Payment', 'fm-qr-code-gateway' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'fm-qr-code-gateway' ),
				'type'        => 'textarea',
				'description' => __( 'Description shown to customers at checkout', 'fm-qr-code-gateway' ),
				'default'     => __( 'Scan the QR code and enter transaction ID to complete order.', 'fm-qr-code-gateway' ),
			),
			'qr_url'      => array(
				'title'       => __( 'QR Code URL', 'fm-qr-code-gateway' ),
				'type'        => 'text',
				'description' => __( 'Enter the URL of the QR code to display at checkout.', 'fm-qr-code-gateway' ),
				'default'     => '',
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
	 * Process_payment
	 *
	 * @param  mixed $order_id order_id.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		// Stop process_payment for Block-based checkout.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wc/store/v1/checkout' ) !== false ) {
			// Return early, do nothing.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		// Classic checkout processing.
		// phpcs:disable WordPress.Security.NonceVerification
		$transaction_id = isset( $_POST['fm_qr_transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fm_qr_transaction_id'] ) ) : '';
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

