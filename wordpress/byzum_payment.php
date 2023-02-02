add_action( 'plugins_loaded', 'woocommerce_bizum_init', 0 );

function woocommerce_bizum_init() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class WC_Bizum extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'bizum';
            $this->method_title       = __( 'Bizum Payment', 'woocommerce' );
            $this->method_description = __( 'Allow customers to pay with Bizum', 'woocommerce' );

            $this->init_form_fields();
            $this->init_settings();

            $this->title              = $this->get_option( 'title' );
            $this->description        = $this->get_option( 'description' );
            $this->instructions       = $this->get_option( 'instructions' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Bizum Payment', 'woocommerce' ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default'     => __( 'Bizum Payment', 'woocommerce' ),
                    'desc_tip'    => true
                ),
                'description' => array(
                    'title'       => __( 'Description', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                    'default'     => __( 'Pay with Bizum', 'woocommerce' ),
                    'desc_tip'    => true
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', 'woocommerce' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
                    'default'     => '',
                    'desc_tip'    => true
                )
            );
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            $order->update_status( 'on-hold', __( 'Awaiting Bizum payment', 'woocommerce' ) );

            return

rray(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url( true )
            );
        }
    }

    function add_bizum_gateway( $methods ) {
        $methods[] = 'WC_Bizum';
        return $methods;
    }
    add_filter( 'woocommerce_payment_gateways', 'add_bizum_gateway' );
}
