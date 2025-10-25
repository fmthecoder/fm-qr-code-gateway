<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
final class FM_QR_Code_Gateway_Blocks extends AbstractPaymentMethodType {
    private $gateway;
    protected $name = 'fm-qr-code-gateway';
    
    public function initialize() {
        $this->settings = get_option( 'woocommerce_fm-qr-code-gateway_settings', [] );
        $this->gateway  = new FM_QR_Code_Gateway_WC();
    }
    
    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'fm-qr-code-gateway-blocks-integration',
            plugin_dir_url(__FILE__) . 'assets/js/fm-qr-checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            '1.0.0',
            true
        );

        if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'fm-qr-code-gateway-blocks-integration');
        }
        
        return [ 'fm-qr-code-gateway-blocks-integration' ];
    }
    public function get_payment_method_data() {
        return [
            'title'       => $this->gateway->title,
            'description' => $this->gateway->description,
            'qr_url'      => $this->gateway->qr_url,
            'meta'        => [
                'fm_qr_transaction_id' => '',
            ],
        ];
    }
}
?>