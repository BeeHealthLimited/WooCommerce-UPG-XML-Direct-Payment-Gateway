<?php
class WC_Gateway_UPG_api extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->id               = 'UPG_api';
        $this->icon             = apply_filters( 'woocommerce_upg_icon', plugins_url('upg.png', __FILE__) );
        $this->has_fields       = true;
        $this->liveurl          = 'https://www.secure-server-hosting.com/secutran/api.php';
        $this->testurl          = 'https://test.secure-server-hosting.com/secutran/api.php';
        $this->method_title     = __( 'UPG plc api', 'woocommerce' );
        
        // Let Woocommerce know what features we support
        $this->supports         = array('default_credit_card_form', 'products', 'refunds');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title            = $this->get_option( 'title' );
        $this->description      = $this->get_option( 'description' );
        $this->reference        = $this->get_option( 'reference' );
        $this->checkcode        = $this->get_option( 'checkcode' );
        $this->testmode         = isset( $this->settings['testmode'] ) && $this->settings['testmode'] == 'yes' ? 'yes' : 'no';
        
        // Define user set messages
        $this->database_error   = $this->get_option( 'database_error' );
        $this->invalid_login    = $this->get_option( 'invalid_login' );
        $this->no_xml_passed    = $this->get_option( 'no_xml_passed' );
        $this->bad_xml_passed   = $this->get_option( 'bad_xml_passed' );
        $this->data_error       = $this->get_option( 'data_error' );
        $this->credit_error     = $this->get_option( 'credit_error' );
        $this->upg_error        = $this->get_option( 'upg_error' );
        $this->transation_error = $this->get_option( 'transation_error' );
        $this->error_reason     = isset( $this->settings['error_reason'] ) && $this->settings['error_reason'] == 'yes' ? 'yes' : 'no';
        
        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ), 0);
        add_action( 'woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action( 'woocommerce_credit_card_form_start', array( $this, 'before_cc_form' ) );
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                            'title'         => __( 'Enabled', 'woocommerce' ),
                            'type'          => 'checkbox',
                            'label'         => __( 'Enable UPG api', 'woocommerce' ),
                            'default'       => 'no'
                        ),
            'defaultoptions' => array(
                            'title'       => __( 'Default options', 'woocommerce' ),
                            'type'        => 'title',
                            'description' => '',
                        ),
            'title' => array(
                            'title'         => __( 'Title', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                            'default'       => __( 'Credit/Debit Card', 'woocommerce' )
                        ),
            'description' => array(
                            'title'         => __( 'Description', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                            'default'       => __( 'Credit/Debit Card', 'woocommerce' )
                        ),
            'reference' => array(
                            'title'         => __( 'Reference', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'Your UPG account reference.', 'woocommerce' ),
                            'default'       => '',
                            'placeholder'   => 'SH2XXXXX'
                        ),
            'checkcode' => array(
                            'title'         => __( 'Check Code', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'The check code for your UPG account.', 'woocommerce' ),
                            'default'       => '',
                            'placeholder'   => 'XXXXXX'
                        ),
            'testmode' => array(
                            'title'         => __( 'Test Mode', 'woocommerce' ),
                            'type'          => 'checkbox',
                            'label'         => __( 'Run UPG in test mode', 'woocommerce' ),
                            'default'       => 'yes',
                            'description'   => __( 'UPG test mode can be used to test payments.', 'woocommerce' )
                        ),
            'notifications' => array(
                            'title'       => __( 'Notification options', 'woocommerce' ),
                            'type'        => 'title',
                            'description' => __( 'These control the error messages which the user sees during checkout.', 'woocommerce' ),
                        ),
            'database_error' => array(
                            'title'         => __( 'DATABASE_ERROR', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'There has been a problem talking to the UPG database. If you see this issue, please contact UPG\'s support department.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: DBE).', 'woocommerce' )
                        ),
            'invalid_login' => array(
                            'title'         => __( 'INVALID_LOGIN', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'The authentication credentials supplied are invalid.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: ILD).', 'woocommerce' )
                        ),
            'no_xml_passed' => array(
                            'title'         => __( 'NO_XML_PASSED', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'No XML as been supplied inside the post header field “xmldoc”.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: NXP).', 'woocommerce' )
                        ),
            'bad_xml_passed' => array(
                            'title'         => __( 'BAD_XML_PASSED', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'The XML supplied is improperly formatted and cannot be parsed.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: BXP).', 'woocommerce' )
                        ),
            'data_error' => array(
                            'title'         => __( 'DATA_ERROR', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'There is a problem with the request data supplied.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: DAE).', 'woocommerce' )
                        ),
            'credit_error' => array(
                            'title'         => __( 'CREDIT_ERROR', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'The account does not have enough transaction credits to perform the request.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: CAE).', 'woocommerce' )
                        ),
            'upg_error' => array(
                            'title'         => __( 'UPG_ERROR', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'There’s been an error talking to our gateway system.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: UPE).', 'woocommerce' )
                        ),
            'transation_error' => array(
                            'title'         => __( 'TRANSACTION_ERROR', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'There is a transaction problem, an additional node called "reason" will contain a textual description of the error.', 'woocommerce' ),
                            'default'       => __( 'An error has occurred while processing your payment, please contact our sales team for assistance (Reference: TRE).', 'woocommerce' )
                        ),
            'error_reason' => array(
                            'title'         => __( 'REASON', 'woocommerce' ),
                            'type'          => 'checkbox',
                            'label'         => __( 'Display "reason"', 'woocommerce' ),
                            'default'       => 'yes',
                            'description'   => __( 'Display the reason for the TRANSACTION_ERROR.', 'woocommerce' )
                        )
            );
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     */
    public function admin_options() {
        
        ?>
        <style type="text/css">
            @font-face{
            font-family: 'rabiohead';
            src: url(https://jupiter.upg.co.uk/secutran/css/rabiohead.ttf);
            src: url("https://jupiter.upg.co.uk/secutran/css/rabiohead-webfont.eot");
            src: url("https://jupiter.upg.co.uk/secutran/css/rabiohead-webfont.eot?#iefix") format("embedded-opentype"), url("https://jupiter.upg.co.uk/secutran/css/rabiohead-webfont.woff") format("woff"), url("https://jupiter.upg.co.uk/secutran/css/rabiohead-webfont.ttf") format("truetype"), url("https://jupiter.upg.co.uk/secutran/css/rabiohead-webfont.svg#RabioheadRegular") format("svg");
            font-weight: normal;
            font-style: normal;
            }
            h4{ font-family: rabiohead; font-size: 26px; margin: 0; }
            h4 sup{ font-size: 10px; }
            .upgtext{ font-family: proxima-nova, Arial, Helvetica, sans-serif; }
        </style>
        <h3><img src="<? echo apply_filters( 'woocommerce_upg_icon', plugins_url('upg.png', __FILE__) ); ?>" border="0" alt="UPG plc" /></h3>
        <h4><?php _e( 'Powering Payments<sup>TM</sup>', 'woocommerce' ); ?></h4>
        <p class="upgtext"><?php _e( 'UPG provides fast, secure, independent connection to the bank of your choice. We take care of <br />the complexities of payment processing, leaving you to concentrate on your success.', 'woocommerce' ); ?></p>
        <p class="upgtext"><a href="http://www.upg.co.uk/" target="_blank">http://www.upg.co.uk/</a></p>

            <table class="form-table">
            <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
            ?>
            </table><!--/.form-table-->

        <?php 
    }

    function get_upg_api_url(){
        if($this->testmode == 'yes'){
            return $this->testurl;
        }
        return $this->liveurl;
    }
    
    public function before_cc_form( $gateway_id ) {
        global $woocommerce;
        
        if ( $gateway_id === $this->id && $this->testmode == 'yes' ) {
            echo '<strong>Gateway is running in test mode</strong></br>Enter the following details to test:</br><strong>Address:</strong> 4-7 The Hay Market, Grantham,NG32 4HG</br><strong>Card No:</strong> 5434 8499 9999 9993</br><strong>CV2:</strong> 557</br><strong>Expiry:</strong> 12/15</br></br>';
        }
    }
    
    public function process_payment( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );
        
        $response = simplexml_load_string( $this->talk_to_upg($this->get_payment_xml( $order_id )));
        if ((string)$response->status == 'OK'){
            $tran_id = sanitize_text_field( $response->reference );
            $tran_cv = sanitize_text_field( $response->cv2avsresult );
            $tran_ca = sanitize_text_field( $response->cardtype );
            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();
            $order->add_order_note( __('TRANSATION: '.$tran_id.' - CV2: '.$tran_cv.' - CARD: '.$tran_ca , 'woothemes') );
            $order->payment_complete( $tran_id );
            
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }else{
            $this->handle_payment_error($response->statustext, $response->reason);
            return;
        }
    }
    
    function handle_payment_error($error_id = '', $tran_reason = ''){

        $errorArray = array(
            'DATABASE_ERROR'        => $this->database_error,
            'INVALID_LOGIN'         => $this->invalid_login,
            'NO_XML_PASSED'         => $this->no_xml_passed,
            'BAD_XML_PASSED'        => $this->bad_xml_passed,
            'DATA_ERROR'            => $this->data_error,
            'CREDIT_ERROR'          => $this->credit_error,
            'UPG_ERROR'             => $this->upg_error,
            'TRANSACTION_ERROR'     => $this->transation_error
        );

        $errorMsg = $errorArray[(string)$error_id].'</br>';
        if ((string)$error_id === 'TRANSACTION_ERROR' && (string)$this->error_reason === 'yes'){
            $errorMsg .= '</br>Additional error information: '.$tran_reason;
        }
        
        return wc_add_notice( $errorMsg, 'error' );
    }
    
    public function process_refund( $order_id, $amount = null, $reason = '', $refund_pass = '' ) {

        $this->order = new WC_Order( $order_id );
        $this->transaction_id = $this->order->get_transaction_id();
        
        if ( ! $this->transaction_id ) { return $this->handle_refund_error('TRANSACTION_ID'); }
        if ( $refund_pass == '' ) { return $this->handle_refund_error('UPG_PASS'); }
        
        $response = simplexml_load_string( $this->talk_to_upg($this->get_refund_xml( $this->transaction_id, $amount, $refund_pass )) );
        if ((string)$response->status == 'OK'){
            return true;
        }else{
           return $this->handle_refund_error($response->statustext);
        }
    }
    
    function handle_refund_error($error_id = ''){
        
        $errorArray = array(
            'TRANSACTION_ID'        => ': Transaction ID is missing.',
            'UPG_PASS'              => ': UPG Password is missing.',
            'DATABASE_ERROR'        => ': Please contact support (ref DBE).',
            'INVALID_LOGIN'         => ': Invalid login details (ref ILD).',
            'NO_XML_PASSED'         => ': Please contact support (ref NXP).',
            'BAD_XML_PASSED'        => ': Please contact support (ref BXP).',
            'DATA_ERROR'            => ': Please contact support (ref DAE).',
            'CREDIT_ERROR'          => ': Please contact support (ref CAE).',
            'UPG_ERROR'             => ': Please contact support (ref UPE).',
            'TRANSACTION_ERROR'     => ': Please contact support (ref TRE).',
        );
        
        return new WP_Error( 'upg_refund_error', sprintf(__('Credit Card Refund failed%s', 'woocommerce' ),$errorArray[(string)$error_id] ) );
    }
    
    function talk_to_upg($xmlDocument){
        $postField = "xmldoc=" . urlencode($xmlDocument);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->get_upg_api_url());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $xmlResponse = trim(curl_exec($ch));
        curl_close($ch);
        return $xmlResponse;
    }
    
    function get_refund_xml( $transaction_id, $amount, $refund_pass  ) {
        global $woocommerce;

        $xmlContrsuct  = '<?xml version ="1.0"?>';
        $xmlContrsuct .= '<request>';
        $xmlContrsuct .= '<type>refund</type>';
        $xmlContrsuct .= '<authentication>';
        $xmlContrsuct .= '<shreference>'.$this->reference.'</shreference>';
        $xmlContrsuct .= '<password>'.$refund_pass.'</password>';
        $xmlContrsuct .= '</authentication>';
        $xmlContrsuct .= '<transaction>';
        $xmlContrsuct .= '<reference>'.$transaction_id.'</reference>';
        $xmlContrsuct .= '<amount>'.$amount.'</amount>';
        $xmlContrsuct .= '</transaction>';
        $xmlContrsuct .= '</request>';

        return $xmlContrsuct;
    }
    
    function get_payment_xml( $order_id ) {
        global $woocommerce;
        global $product;
        $order = new WC_Order( $order_id );
        $Secuitems = '';
        foreach($order->get_items() AS $item){
            $Options = array();
           
            foreach($item As $Attribute => $Value){
                if(!in_array($Attribute, $this->GetStandardProductFields())){
                    $Options[$Attribute] = $Value;
                }
            }
            
            $product = new WC_Product($item['product_id']);
            
            $Secuitems .= '['.$item['product_id'].'|'.$product->get_sku().'|'.$item['name'];
            if(!empty($Options)){
                foreach($Options AS $Key => $Value){
                    if ((string)$Key === 'pa_quantity'){
                        $Key = 'Quantity';
                    }
                    $Secuitems .= ', '.$Key.': '.$Value;
                }
            }
            $Secuitems .= '|'.$this->currency_format($item['line_total']/$item['qty'])
                    .'|'.$item['qty'].'|'.$this->currency_format($item['line_total']).']';
        }
        
        $TransactionSubTotal = $this->currency_format($order->get_subtotal());
        $TransactionAmount = $this->currency_format($order->get_total());
        
        $expirydate =   str_replace( array( '/', ' '), '', $_POST['UPG_api-card-expiry'] );
        $cardnumber =   str_replace( array(' ', '-' ), '', $_POST['UPG_api-card-number'] );
        $cardcv2 =      str_replace( array(' ', '-' ), '', $_POST['UPG_api-card-cvc'] );
        
        $xmlContrsuct  = '<?xml version ="1.0"?>';
        $xmlContrsuct .= '<request>';
        $xmlContrsuct .= '<type>transaction</type>';
        $xmlContrsuct .= '<authtype>authorise</authtype>';
        $xmlContrsuct .= '<authentication>';
        $xmlContrsuct .= '<shreference>'.$this->reference.'</shreference>';
        $xmlContrsuct .= '<checkcode>'.$this->checkcode.'</checkcode>';
        $xmlContrsuct .= '</authentication>';
        $xmlContrsuct .= '<transaction>';
        //Card details
        $xmlContrsuct .= '<cardnumber>'.$cardnumber.'</cardnumber>';
        $xmlContrsuct .= '<cv2>'.$cardcv2.'</cv2>';
        $xmlContrsuct .= '<cardexpiremonth>'.substr($expirydate, 0, 2).'</cardexpiremonth>';
        $xmlContrsuct .= '<cardexpireyear>'.substr($expirydate, -2).'</cardexpireyear>';
        //Cardholder details
        $xmlContrsuct .= '<cardholdersname>'.$order->billing_first_name.' '.$order->billing_last_name.'</cardholdersname>';
        $xmlContrsuct .= '<cardholdersemail>'.$order->billing_email.'</cardholdersemail>';
        $xmlContrsuct .= '<cardholderaddr1>'.$order->billing_address_1.'</cardholderaddr1>';
        $xmlContrsuct .= '<cardholderaddr2>'.$order->billing_address_2.'</cardholderaddr2>';
        $xmlContrsuct .= '<cardholdercity>'.$order->billing_city.'</cardholdercity>';
        $xmlContrsuct .= '<cardholderstate>'.$order->billing_state.'</cardholderstate>';
        $xmlContrsuct .= '<cardholderpostcode>'.$order->billing_postcode.'</cardholderpostcode>';
        $xmlContrsuct .= '<cardholdercountry>'.$order->billing_country.'</cardholdercountry>';
        $xmlContrsuct .= '<cardholdertelephonenumber>'.$order->billing_phone.'</cardholdertelephonenumber>';
        // Order details
        $xmlContrsuct .= '<orderid>'.$order->id.'</orderid>';
        $xmlContrsuct .= '<subtotal>'.$TransactionSubTotal.'</subtotal>';
        $xmlContrsuct .= '<transactionamount>'.$TransactionAmount.'</transactionamount>';
        $xmlContrsuct .= '<transactioncurrency>'.get_woocommerce_currency().'</transactioncurrency>';
        $xmlContrsuct .= '<transactiondiscount>'.$this->currency_format($order->get_total_discount()).'</transactiondiscount>';
        $xmlContrsuct .= '<transactiontax>'.$this->currency_format($order->get_total_tax()).'</transactiontax>';
        $xmlContrsuct .= '<shippingcharge>'.$this->currency_format($order->get_total_shipping()).'</shippingcharge>';
        $xmlContrsuct .= '<secuitems><![CDATA['.$Secuitems.']]></secuitems>';
        
        $xmlContrsuct .= '</transaction>';
        $xmlContrsuct .= '</request>';
        
        return $xmlContrsuct;
        
    }
    
    function currency_format($Number){
        return number_format($Number, 2, '.', '');
    }

    function GetStandardProductFields(){
        return array(
            'product_id',
            'name',
            'line_total',
            'line_tax_data',
            '_line_tax_data',
            'wc_cog_item_cost',
            'wc_cog_item_total_cost',
            'qty',
            'type',
            'item_meta',
            'line_subtotal_tax',
            'line_tax',
            'line_subtotal',
            'variation_id',
            'tax_class',
            'pa_booking-class',
            'standard-class-adult',
            'tmcartepo_data',
            'gravity_forms_history',
            '_gravity_form_data',
            'display_description',
            'disable_woocommerce_price',
            'price_before',
            'price_after',
            'disable_calculations',
            'disable_label_subtotal',
            'disable_label_options',
            'disable_label_total',
            'label_subtotal',
            'Subtotal',
            'label_options',
            'Options',
            'label_total',
            'Total'
        );
    }
}