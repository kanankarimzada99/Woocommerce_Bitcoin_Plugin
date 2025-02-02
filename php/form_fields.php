
<?php

class FormFields {
    public static function init_form_fields($callback_url = '') {
        $blockonomics = new Blockonomics;
        $cryptos = $blockonomics->getSupportedCurrencies();

        $form_fields = array(
            'enabled' => array(
                'title' => __('Checkout<p class="block-title-desc">Payment method settings for the woocomerce checkout page</p>', 'blockonomics-bitcoin-payments'),
                'subtitle' => __('Enable Blockonomics plugin', 'blockonomics-bitcoin-payments'),
                'type' => 'checkbox',
                'label' => __('Enable Blockonomics as a payment method during checkout', 'blockonomics-bitcoin-payments'),
                'default' =>  get_option('blockonomics_api_key') ? 'yes' : 'no' 
            ),
            'title' => array(
                'subtitle' => __('Title', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'description' => __('Payment method for <i>bitcoin</i> displayed to the user during checkout.', 'blockonomics-bitcoin-payments'),
                'default' => __('Bitcoin', 'blockonomics-bitcoin-payments'),
                'placeholder' => __('Title', 'blockonomics-bitcoin-payments')
            ),
            'description' => array(
                'subtitle' => __('Description', 'blockonomics-bitcoin-payments'),
                'type' => 'text',
                'description' => __('Payment method <i>description</i> displayed to the user during checkout.', 'blockonomics-bitcoin-payments'),
                'default' => '',
                'placeholder' => __('Description', 'blockonomics-bitcoin-payments')
            ),
            'api_key' => array(
                'title' => __('
                    Store
                    <p class="block-title-desc">Setup a Blockonomics store to start accepting payments in your wallet</p>
                    ', 'blockonomics-bitcoin-payments'),
                'subtitle' => __('API Key', 'blockonomics-bitcoin-payments'),
                'type' => 'apikey',
                'description' => __('Setup Store on <a href="https://blockonomics.co/merchants" target="_blank" style="color: green;">Blockonomics</a> and paste API Key here', 'blockonomics-bitcoin-payments'),
                'default' => get_option('blockonomics_api_key'),
                'placeholder' => __('API key', 'blockonomics-bitcoin-payments'),
            ),
            'testsetup' => array(
                'id'    => 'testsetup',
                'type'  => 'testsetup',
            )
        );

        $firstItem = true;
        foreach ($cryptos as $currencyCode => $crypto) {
            $title = $firstItem ? 'Currencies<p class="block-title-desc">Setting and testing currencies accepted </p>' : '';
            $default = $currencyCode === 'btc' ? 1 : 0;
            $form_fields[$currencyCode . '_enabled'] = array(
                'title'   => $title,
                'type'    => 'checkbox',
                'subtitle'   => $crypto["name"] . ' (' . strtoupper($currencyCode) . ')',
                'label'   => __('Enable accepting '.$crypto["name"]),
                'default' => get_option('blockonomics_' . $currencyCode, $default) == 1 ? 'yes' : 'no',
                'add_divider' => $firstItem
            );
            $firstItem = false;
        }

        $form_fields['extra_margin'] = array(
            'title' => __('Advanced<p class="block-title-desc">Setting for advanced control</p>', 'blockonomics-bitcoin-payments'),
            'type' => 'number',
            'description' => __('Increase live fiat to BTC rate by small percent', 'blockonomics-bitcoin-payments'),
            'subtitle' => __('Extra Currency Rate Margin %', 'blockonomics-bitcoin-payments'),
            'default' => get_option('blockonomics_extra_margin', 0),
            'placeholder' => __('Extra Currency Rate Margin %', 'blockonomics-bitcoin-payments'),
            'add_divider' => true
        );
        $form_fields['underpayment_slack'] = array(
            'title' => __('', 'blockonomics-bitcoin-payments'),
            'type' => 'number',
            'label' => __('Under Payment', 'blockonomics-bitcoin-payments'),
            'description' => __('Allow payments that are off by a small percentage', 'blockonomics-bitcoin-payments'),
            'subtitle' => __('Underpayment Slack %', 'blockonomics-bitcoin-payments'),
            'default' =>  get_option('blockonomics_underpayment_slack', 0),
            'placeholder' => __('Underpayment Slack %', 'blockonomics-bitcoin-payments')
        );
        $form_fields['no_javascript'] = array(
            'title' => __('', 'blockonomics-bitcoin-payments'),
            'type' => 'checkbox',
            'subtitle' => __('No Javascript checkout page', 'blockonomics-bitcoin-payments'),
            'label' => __('Enable this if you have majority customer that uses tor like browser that blocks JS', 'blockonomics-bitcoin-payments'),
            'default' => get_option('blockonomics_nojs') == 1 ? 'yes' : 'no',
        );
        $form_fields['partial_payment'] = array(
            'title' => __('', 'blockonomics-bitcoin-payments'),
            'type' => 'checkbox',
            'subtitle' => __('Partial Payments', 'blockonomics-bitcoin-payments'),
            'label' => __('Allow customer to pay order via multiple payment  ', 'blockonomics-bitcoin-payments'),
            'default' => $blockonomics->is_partial_payments_active() ? 'yes' : 'no',
        );
        $form_fields['network_confirmation'] = array(
            'title' => __('', 'blockonomics-bitcoin-payments'),
            'subtitle' => __('Network Confirmations', 'blockonomics-bitcoin-payments'),
            'type' => 'select',
            'description' => __('Network Confirmations required for payment to complete', 'blockonomics-bitcoin-payments'),
            'default' => get_option('blockonomics_network_confirmation', 2),
            'options' => array(
                '2' => __('2(Recommended)', 'blockonomics-bitcoin-payments'),
                '1' => __('1', 'blockonomics-bitcoin-payments'),
                '0' => __('0', 'blockonomics-bitcoin-payments'),
            ),
        );
        $form_fields['call_backurls'] = array(
            'title' => __('', 'blockonomics-bitcoin-payments'),
            'type' => 'text',
            'description' => __('You need this callback URL to setup multiple stores', 'blockonomics-bitcoin-payments'),
            'subtitle' => __('Callback URL', 'blockonomics-bitcoin-payments'),
            'default' => $callback_url,
            'disabled' => true,
            'css' => 'width:100%;',
        );
        return $form_fields;
    }
}