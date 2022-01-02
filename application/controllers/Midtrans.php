<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Midtrans extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->model('Midtrans_model');
        $params = array('server_key' => 'SB-Mid-server-EdyF1qz8Y7TgcbfjS0aVWUem', 'production' => false);
        $this->load->library('veritrans');
        $this->veritrans->config($params);
    }


    public function index()
    {
        $data = [
            'semuaproduk'   => $this->Midtrans_model->getallproduk(),
            'keranjang'     => $this->Midtrans_model->getallcart(),
            'semuatransaksi'=> $this->Midtrans_model->getAllTransaction()
        ];
        $this->load->view('midtrans/cart', $data);
    }


    public function cekstatus()
    {
        $orderid    = $this->input->post('order_id');
        if($orderid){
            $this->status($orderid);
        }else{
            echo 'order id tidak ada';
        }
    }

    private function status($orderid)
    {
        $result     = $this->veritrans->status($orderid);
        $dataupdate = [
            'transaction_status'  => $result->transaction_status,
            'date_modified'       => time()
        ];

        $where      = [
            'order_id'  => $orderid
        ];

        $update     = $this->Midtrans_model->update($dataupdate, $where);
        if($update > 0){
            $this->session->set_flashdata('messagetransaksi', 'Data transaksi berhasil diupdate');
        }else{
            $this->session->set_flashdata('messagetransaksi', 'Server sedang sibuk, silahkan coba beberapa saat lagi');
        }
        redirect('midtrans');
    }
    public function simpan()
    {
        $productid  = $this->input->post('produkid');
        $jumlah  = $this->input->post('jumlah');
        $datainsert =   [
            'product_id'   => $productid,
            'jumlah'       => $jumlah,
            'status'       => 0
        ];

        $insert     = $this->Midtrans_model->insertcart($datainsert);
        if ($insert > 0) {
            $this->session->set_flashdata('message', 'Data keranjang berhasil ditambah');
        } else {
            $this->session->set_flashdata('message', 'Server sedang sibuk, silahkan coba lagi');
        }
        redirect('midtrans');
    }
}
