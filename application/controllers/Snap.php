<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Snap extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$params = array('server_key' => 'SB-Mid-server-EdyF1qz8Y7TgcbfjS0aVWUem', 'production' => false);
		$this->load->library('midtrans');
		$this->midtrans->config($params);
		$this->load->helper('url');
		$this->load->model('Midtrans_model');
	}

	public function index()
	{
		$this->load->view('checkout_snap');
	}
	public function token()
	{
		$keranjang			= $this->Midtrans_model->getallcart();
		$grossamount		= $this->input->get('amount');
		$item_details		= array();
		foreach ($keranjang as $krjg) {
		$item_details[] = [
            'id'		=> $krjg['id'],
            'price'		=> $krjg['price'],
            'quantity'	=> $krjg['jumlah'],
            'name'		=> $krjg['product']
			];
		}
		// Required
		$transaction_details = array(
			'order_id' => rand(),
			'gross_amount' => $grossamount, // no decimal allowed for creditcard
		);

		//Optional
		// $billing_address = array(
		// 	'first_name'    => "Andri Test",
		// 	'last_name'     => "Litani",
		// 	'address'       => "Mangga 20",
		// 	'city'          => "Jakarta",
		// 	'postal_code'   => "16602",
		// 	'phone'         => "081122334455",
		// 	'country_code'  => 'IDN'
		// );
		// // Optional
		// $shipping_address = array(
		// 	'first_name'    => "Obet",
		// 	'last_name'     => "Supriadi",
		// 	'address'       => "Manggis 90",
		// 	'city'          => "Jakarta",
		// 	'postal_code'   => "16601",
		// 	'phone'         => "08113366345",
		// 	'country_code'  => 'IDN'
		// );

		// // Optional
		// $customer_details = array(
		// 	'first_name'    => "Andri",
		// 	'last_name'     => "Litani",
		// 	'email'         => "hardiyantoagung55@gmail.com",
		// 	'phone'         => "081122334455",
		// 	'billing_address'  => $billing_address,
		// 	'shipping_address' => $shipping_address
		// );
		// Data yang akan dikirim untuk request redirect_url.
		$credit_card['secure'] = true;
		//ser save_card true to enable oneclick or 2click
		//$credit_card['save_card'] = true;
		$time = time();
		$custom_expiry = array(
			'start_time' 	=> date("Y-m-d H:i:s O", $time),
			'unit' 			=> 'day',
			'duration'  	=> 2
		);

		$transaction_data = array(
			'transaction_details' 	=> $transaction_details,
			'item_details'       	=> $item_details,
			// 'customer_details'   	=> $customer_details,
			'credit_card'        	=> $credit_card,
			'expiry'             	=> $custom_expiry
		);
		error_log(json_encode($transaction_data));
		$snapToken = $this->midtrans->getSnapToken($transaction_data);
		error_log($snapToken);
		echo $snapToken;
	}

	public function finish()
	{
		$keranjang			= $this->Midtrans_model->getallcart();
		foreach ($keranjang as $krjg) {
			$this->db->update('tr_cart', ['status' => 1], ['id' => $krjg['id']]);
		}
		$result = json_decode($this->input->post('result_data'));
		echo 'RESULT <br><pre>';
		var_dump($result);
		echo '</pre>';


		if($result->payment_type == 'bank_transfer'){
            if(@$result->va_numbers){
                foreach ($result->va_numbers as $row){
                    $bank       = $row->bank;
                    $vaNumber   = $row->va_number;
                    $billerCode = '';

                }
            }else{
                    $bank        = 'permata';
                    $vaNumber    = $result->permata_va_number;
                    $billerCode  = '';
            }
        }elseif ($result->payment_type == 'echannel'){
                    $bank        = 'mandiri';
                    $vaNumber    = $result->bill_key;
                    $billerCode  = $result->biller_code;
        }else{
		            $bank        = 'alfamart/indomart';
		            $vaNumber    = $result->payment_code;
		            $billerCode  = '';
        }
		$grossAmount    = str_replace('.00','',$result->gross_amount);
		$dataInput  = [
		    'order_id'          => $result->order_id,
            'gross_amount'      => $grossAmount,
            'payment_type'      => $result->payment_type,
            'bank'              => $bank,
            'va_number'         => $vaNumber,
            'biller_code'       => $billerCode,
            'transaction_status'=> $result->transaction_status,
            'transaction_time'  => $result->transaction_time,
            'pdf_url'           => $result->pdf_url,
            'date_created'      => time(),
            'date_modified'     => time()
        ];

		$this->db->insert('tr_cart_to_checkout',$dataInput);

	}
}