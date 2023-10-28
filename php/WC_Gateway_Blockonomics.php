<?php

/**
 * This class provides the functions needed for extending the WooCommerce
 * Payment Gateway class
 *
 * @class   WC_Gateway_Blockonomics
 * @extends WC_Payment_Gateway
 * @version 2.0.1
 * @author  Blockonomics Inc.
 */
class WC_Gateway_Blockonomics extends WC_Payment_Gateway
{
    public function __construct()
    {
        load_plugin_textdomain('blockonomics-bitcoin-payments', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        $this->id   = 'blockonomics';
        
        include_once 'Blockonomics.php';
        $blockonomics = new Blockonomics;
        $active_cryptos = $blockonomics->getActiveCurrencies();

        if (isset($active_cryptos['btc']) && isset($active_cryptos['bch'])) {
            $this->icon = plugins_url('img', dirname(__FILE__)) . '/bitcoin-bch-icon.png';
        } elseif (isset($active_cryptos['btc'])) {
            $this->icon = plugins_url('img', dirname(__FILE__)) . '/bitcoin-icon.png';
        } elseif (isset($active_cryptos['bch'])) {
            $this->icon = plugins_url('img', dirname(__FILE__)) . '/bch-icon.png';
        }

        $this->has_fields        = false;
        $this->order_button_text = __('Pay with bitcoin', 'blockonomics-bitcoin-payments');

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');

        // Actions
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id, array(
                $this,
                'process_admin_options'
            )
        );
        add_action(
            'woocommerce_receipt_blockonomics', array(
                $this,
                'receipt_page'
            )
        );

        // Payment listener/API hook
        add_action(
            'woocommerce_api_wc_gateway_blockonomics', array(
                $this,
                'handle_requests'
            )
        );

		
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable Blockonomics plugin', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('Show bitcoin as an option to customers during checkout?', 'blockonomics-bitcoin-payments'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Title', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'blockonomics-bitcoin-payments'),
                'default' => __('Bitcoin', 'blockonomics-bitcoin-payments')
            ),
            'description' => array(
                'title' => __( 'Description', 'blockonomics-bitcoin-payments' ),
                'type' => 'text',
                'description' => __('This controls the description which the user sees during checkout.', 'blockonomics-bitcoin-payments'),
                'default' => ''
            ),

            'wallet' => array(
                'title' => __('Wallet', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'label'=> __('Temporary Wallet', 'blockonomics-bitcoin-payments'),
                'description' => __('Accepting temporary funds with temporary wallet you can setup a blockonomics store to your wallet', 'blockonomics-bitcoin-payments'),
                'default' => __('Setup Store', 'blockonomics-bitcoin-payments')
            ),
            'store' => array(
                'title' => __('Store', 'blockonomics-bitcoin-payments'),
                'description' => __('1) Setup a store at Blockonomics', 'blockonomics-bitcoin-payments'),
                'type' => 'button',
                'default' => __('Setup Store', 'blockonomics-bitcoin-payments')
            ),
            'APIkey' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'text',  // Ensure there's a handler for this type elsewhere in your code.
                'description' => __('2) Get API key for the store and save here ', 'blockonomics-bitcoin-payments'),
                'default' => __(get_option('blockonomics_api_key'), 'blockonomics-bitcoin-payments'),
            ),
            'testsetup' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'button',
                'description' => __('3) Test the setup to check it is working correctly ', 'blockonomics-bitcoin-payments'),
                'default' => 'Test'
            ),
            'btc_enabled' => array(
                'title' => __('Currencies', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('Bitcoin (BTC)', 'blockonomics-bitcoin-payments'),
                'default' => 'no'
            ),
            'bch_enabled' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('Bitcoin Cash (BCH)', 'blockonomics-bitcoin-payments'),
                'default' => 'no'
            ),
            'extra_margin' => array(
                'title' => __('Advanced', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'description' => __('Extra Currency Rate Margin % (Increase live fiat to BTC rate by small percent)', 'blockonomics-bitcoin-payments'),
                'default' => '0'
            ),
            'underpayment_slack' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'description' => __('Underpayment Slack %.Allow payments that are off by a small percentage', 'blockonomics-bitcoin-payments'),
                'default' => "aishwarya"
            ),
            'no_javscript' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('No javscript checkout page', 'blockonomics-bitcoin-payments'),
                'description' => __('Enable if majority of the customer are using tor like browser that blocks the JS ', 'blockonomics-bitcoin-payments'),
                'default' => 'no'
            ),
            'partialpayment' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('Partial Payment ', 'blockonomics-bitcoin-payments'),
                'description' => __('Allow customer to pay order via multiple payement  ', 'blockonomics-bitcoin-payments'),
                'default' => 'no'
            ),
            'network_confirmation' => array(
                'title' => __('', 'blockonomics-bitcoin-payments'),
                'type' => 'select',  // Changed 'dropdownbox' to 'select'
                'description' => __('Network Confirmations required for payment to complete', 'blockonomics-bitcoin-payments'),
                // 'options'     => $this->load_shipping_method_options(),
                'default' => __('2 Recommended', 'blockonomics-bitcoin-payments'),
            ),
            'callBackurl' => array(
                'title' => __(' ', 'blockonomics-bitcoin-payments'),
                'type' => 'text',  // Ensure there's a handler for this type elsewhere in your code.
                'description' => __('Callback URL.You need this callback URL to setup multiple stores ', 'blockonomics-bitcoin-payments'),
                // 'default' => __($this->get_callback_url(), 'blockonomics-bitcoin-payments'),
                'disabled' => true
            ),
            
        );
    }

    public function process_admin_options()
    {
        if (!parent::process_admin_options()) {
            return false;
        }
    }
    
    // Woocommerce process payment, runs during the checkout
    public function process_payment($order_id)
    {
        include_once 'Blockonomics.php';
        $blockonomics = new Blockonomics;
        
        $order_url = $blockonomics->get_order_checkout_url($order_id);

        return array(
            'result'   => 'success',
            'redirect' => $order_url
        );
    }

    // Handles requests to the blockonomics page
    // Sanitizes all request/input data
    public function handle_requests()
    {
        $crypto = isset($_GET["crypto"]) ? sanitize_key($_GET['crypto']) : "";
        $finish_order = isset($_GET["finish_order"]) ? sanitize_text_field(wp_unslash($_GET['finish_order'])) : "";
        $get_amount = isset($_GET['get_amount']) ? sanitize_text_field(wp_unslash($_GET['get_amount'])) : "";
        $secret = isset($_GET['secret']) ? sanitize_text_field(wp_unslash($_GET['secret'])) : "";
        $addr = isset($_GET['addr']) ? sanitize_text_field(wp_unslash($_GET['addr'])) : "";
        $status = isset($_GET['status']) ? intval($_GET['status']) : "";
        $value = isset($_GET['value']) ? absint($_GET['value']) : "";
        $txid = isset($_GET['txid']) ? sanitize_text_field(wp_unslash($_GET['txid'])) : "";
        $rbf = isset($_GET['rbf']) ? wp_validate_boolean(intval(wp_unslash($_GET['rbf']))) : "";

        include_once 'Blockonomics.php';
        $blockonomics = new Blockonomics;

        if ($finish_order) {
            $order_id = $blockonomics->decrypt_hash($finish_order);
            $blockonomics->redirect_finish_order($order_id);
        }else if ($get_amount && $crypto) {
            $order_id = $blockonomics->decrypt_hash($get_amount);
            $blockonomics->get_order_amount_info($order_id, $crypto);
        }else if ($secret && $addr && isset($status) && $value && $txid) {
            $blockonomics->process_callback($secret, $addr, $status, $value, $txid, $rbf);
        }

        exit();
    }
}
