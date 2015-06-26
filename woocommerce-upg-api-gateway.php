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
        $this->has_fields       = false;
        $this->liveurl          = 'https://www.secure-server-hosting.com/secutran/api.php';
		$this->testurl          = 'https://test.secure-server-hosting.com/secutran/api.php';
        $this->asurl            = 'https://www.secure-server-hosting.com/secutran/create_secustring.php';
        $this->method_title     = __( 'UPG plc api', 'woocommerce' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title            = $this->get_option( 'title' );
        $this->description      = $this->get_option( 'description' );
        $this->reference        = $this->get_option( 'reference' );
        $this->checkcode        = $this->get_option( 'checkcode' );
        $this->ordstatus        = $this->get_option( 'ordstatus' );
        $this->filename         = $this->get_option( 'filename' );
        $this->phrase           = $this->get_option( 'phrase' );
        $this->referrer         = $this->get_option( 'referrer' );
		$this->testmode         = isset( $this->settings['testmode'] ) && $this->settings['testmode'] == 'yes' ? 'yes' : 'no';

		$this->supports			= array('default_credit_card_form','refunds');
		
        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ), 0);

        add_action( 'woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'check_return' ));
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
            'title' => array(
                            'title'         => __( 'Title', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                            'default'       => __( 'Credit/Debit Card', 'woocommerce' ),
                            'desc_tip'      => true
                        ),
            'description' => array(
                            'title'         => __( 'Description', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                            'default'       => __( 'Credit/Debit Card', 'woocommerce' ),
                            'desc_tip'      => true
                        ),
            'reference' => array(
                            'title'         => __( 'Reference', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'Your UPG account reference.', 'woocommerce' ),
                            'default'       => '',
                            'placeholder'   => 'SH2XXXXX',
                            'desc_tip'      => true
                        ),
            'checkcode' => array(
                            'title'         => __( 'Check Code', 'woocommerce' ),
                            'type'          => 'text',
                            'description'   => __( 'The check code for your UPG account.', 'woocommerce' ),
                            'default'       => '',
                            'placeholder'   => 'XXXXXX',
                            'desc_tip'      => true
                        ),
            'testmode' => array(
                            'title'         => __( 'Test Mode', 'woocommerce' ),
                            'type'          => 'checkbox',
                            'label'         => __( 'Run UPG in test mode', 'woocommerce' ),
                            'default'       => 'yes',
                            'description'   => __( 'UPG test mode can be used to test payments.', 'woocommerce' ),
                            'desc_tip'      => true
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
        h4{
            font-family: rabiohead;
            font-size: 26px;
            margin: 0;
        }
        h4 sup{
            font-size: 10px;
        }
        .upgtext{
            font-family: proxima-nova, Arial, Helvetica, sans-serif;
        }
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

    /**
     * Format number for currency output
     * 
     * @param float $Number
     * @return string
     */
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
	
	function get_upg_api_url(){
        if($this->testmode == 'yes'){
            return $this->testurl;
        } else {
            return $this->liveurl;
        }
    }
	
    /**
     * Process the payment and return the result
     *
     * @access public
     * @param int $order_id
     * @return array
     **/
    function process_payment( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );
		
		$postField = "xmldoc=" . urlencode($this->generateXML( $order_id ));
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $this->get_upg_api_url());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $xmlResponse = trim(curl_exec($ch));
        curl_close($ch);
		
		$response = simplexml_load_string( $xmlResponse );
		if ((string)$response->status == 'OK'){
			// Mark order as Paid and Empty cart
			$UPGreference	= sanitize_text_field( $response->reference );
            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();
            $order->payment_complete( $UPGreference );
			
			return array(
				'result'    => 'success',
				'redirect'  => $this->get_return_url( $order )
			);
		}else{
			$error_message = $response->reason;
			wc_add_notice( __('Payment error: ', 'woocommerce') . $error_message, 'error' );
			return;
		}
         
    }
	
	/**
     * Validate credit card form fields
     *
     * @access      public
     * @return      void
     */
    public function validate_fields() {
        //TODO: Add field validation
    }
	
	/**
     * Process refund
     *
     * Overriding refund method
     *
     * @access      public
     * @param       int $order_id
     * @param       float $amount
     * @param       string $reason
     * @return      mixed True or False based on success, or WP_Error
     */
    public function process_refund( $order_id, $amount = null, $reason = '', $refund_pass = '' ) {
        $this->order = new WC_Order( $order_id );
        $this->transaction_id = $this->order->get_transaction_id();
        if ( ! $this->transaction_id ) {
            return new WP_Error( 'upg_refund_error',
                sprintf(
                    __( '%s Credit Card Refund failed because the Transaction ID is missing.', 'woocommerce' ),
                    get_class( $this )
                )
            );
        }
        if ( $refund_pass == '' ) {
            return new WP_Error( 'upg_refund_error',
                sprintf(
                    __( '%s Credit Card Refund failed because the UPG Password is missing.', 'woocommerce' ),
                    get_class( $this )
                )
            );
        }
		
        $postField = "xmldoc=" . urlencode($this->generateRefundXML( $this->transaction_id, $amount, $refund_pass ));
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $this->get_upg_api_url());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $xmlResponse = trim(curl_exec($ch));
        curl_close($ch);
		
		$response = simplexml_load_string( $xmlResponse );
		$errorMsg = '.';
		if ((string)$response->status == 'OK'){
			return true;
		}else{
			if ((string)$response->statustext == 'INVALID_LOGIN'){
				$errorMsg = ': Invalid login details';
			}
			return new WP_Error( 'upg_refund_error',
                sprintf(__('Credit Card Refund failed please login to UPG and do a manual refund%s', 'woocommerce' ),$errorMsg)
            );
		}
    }
	
	function generateRefundXML( $transaction_id, $amount, $refund_pass  ) {
		global $woocommerce;
		
		$xmlContrsuct = '<?xml version ="1.0"?>';
		$xmlContrsuct .= '<request>';
		$xmlContrsuct .= '<type>refund</type>';
		$xmlContrsuct .= '<authentication>';
		$xmlContrsuct .= '<shreference>'.$this->reference.'</shreference>';
        $xmlContrsuct .= '<password>'.$refund_pass.'</password>';
		$xmlContrsuct .= '</authentication>';
		$xmlContrsuct .= '<transaction>';
		$xmlContrsuct .= '<orderid>'.$transaction_id.'</orderid>';
        $xmlContrsuct .= '<subtotal>'.$amount.'</subtotal>';
		$xmlContrsuct .= '</transaction>';
		$xmlContrsuct .= '</request>';
		
        return $xmlContrsuct;
	}
	
    /**
     * Get arguments for passing to UPG
     **/
    function generateXML( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );
		
        //Product data
        $Secuitems = '';
        foreach($order->get_items() AS $item){
            $Options = array();
           
            foreach($item As $Attribute => $Value){
                if(!in_array($Attribute, $this->GetStandardProductFields())){
                    $Options[$Attribute] = $Value;
                }
            }
            
            $Secuitems .= '['.$item['product_id']
                    .'||'.$item['name'];
            if(!empty($Options)){
                foreach($Options AS $Key => $Value){
                    $Secuitems .= ', '.$Key.': '.$Value;
                }
            }
            $Secuitems .= '|'.$this->currency_format($item['line_total']/$item['qty'])
                    .'|'.$item['qty']
                    .'|'.$this->currency_format($item['line_total']).']';
        }
        
        $TransactionSubTotal = $this->currency_format($order->get_subtotal());
        $TransactionAmount = $this->currency_format($order->get_total());
        
        $xmlContrsuct = '<?xml version ="1.0"?>';
		$xmlContrsuct .= '<request>';
		$xmlContrsuct .= '<type>transaction</type>';
		$xmlContrsuct .= '<authtype>authorise</authtype>';
		$xmlContrsuct .= '<authentication>';
		$xmlContrsuct .= '<shreference>'.$this->reference.'</shreference>';
        $xmlContrsuct .= '<checkcode>'.$this->checkcode.'</checkcode>';
		$xmlContrsuct .= '</authentication>';
		$xmlContrsuct .= '<transaction>';
		//Card details
		
		$expirydate = 	'0116';//str_replace( array( '/', ' '), '', $_POST['UPG_api-card-expiry'] );
		$cardnumber = 	'4929421234600821';//str_replace( array(' ', '-' ), '', $_POST['UPG_api-card-number'] );
		$cardcv2 = 		'356';//str_replace( array(' ', '-' ), '', $_POST['UPG_api-card-cvc'] );
		
		$xmlContrsuct .= '<cardnumber>'.$cardnumber.'</cardnumber>';
		$xmlContrsuct .= '<cardexpiremonth>'.substr($expirydate, 0, 2).'</cardexpiremonth>';
		$xmlContrsuct .= '<cardexpireyear>'.substr($expirydate, 2).'</cardexpireyear>';
		$xmlContrsuct .= '<cv2>'.$cardcv2.'</cv2>';
        //Cardholder details
        $xmlContrsuct .= '<cardholdersname>'.$order->billing_first_name.' '.$order->billing_last_name.'</cardholdersname>';
        $xmlContrsuct .= '<cardholdersemail>'.$order->billing_email.'</cardholdersemail>';
        $xmlContrsuct .= '<cardholderaddr1>6347</cardholderaddr1>';
        $xmlContrsuct .= '<cardholderaddr2>'.$order->billing_address_2.'</cardholderaddr2>';
        $xmlContrsuct .= '<cardholdercity>'.$order->billing_city.'</cardholdercity>';
        $xmlContrsuct .= '<cardholderstate>'.$order->billing_state.'</cardholderstate>';
        $xmlContrsuct .= '<cardholderpostcode>178</cardholderpostcode>';
        $xmlContrsuct .= '<cardholdercountry>'.$order->billing_country.'</cardholdercountry>';
        $xmlContrsuct .= '<cardholdertelephonenumber>'.$order->billing_phone.'</cardholdertelephonenumber>';

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
}