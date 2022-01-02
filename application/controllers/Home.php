<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Home_model');
        $this->keyrajaongkir = '1727a921b6f54b25bfbc4c756255a0d0';
        $this->kabupatenRajaOngkir = 'https://api.rajaongkir.com/starter/city?key=' . $this->keyrajaongkir;
    }

    public function index()
    {
        $this->form_validation->set_rules('kotaasalrajaongkir', 'Kota Asal', 'trim|required');
        $this->form_validation->set_rules('kotatujuanrajaongkir', 'Kota Tujuan', 'trim|required');
        $this->form_validation->set_rules('beratkirim', 'Berat Pengiriman', 'trim|required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('index');
        } else {

            $berat = $this->input->post('beratkirim') * 1000;
            if ($berat > 30000) {
                $this->session->set_flashdata('pesan', 'Maaf berat maksimal 30 Kg yang bisa kami cek :(');
                redirect('home');
            } else {
                $kabupatenrajaongkir = json_decode(file_get_contents($this->kabupatenRajaOngkir));
                $kotaasal = $this->input->post('kotaasalrajaongkir');
                $kotatujuan = $this->input->post('kotatujuanrajaongkir');


                $pisahkabkotaasal = str_replace('KAB. ', '', $kotaasal);
                $pisahkotakotaasal = str_replace('KOTA ', '', $pisahkabkotaasal);
                $kotaasalbaru = ucwords(strtolower($pisahkotakotaasal));

                $pisahkabkotatujuan = str_replace('KAB. ', '', $kotatujuan);
                $pisahkotakotatujuan = str_replace('KOTA ', '', $pisahkabkotatujuan);
                $kotatujuanbaru = ucwords(strtolower($pisahkotakotatujuan));
                $datakabupatenrajaongkir = $kabupatenrajaongkir->rajaongkir->results;
                foreach ($datakabupatenrajaongkir as $row) {
                    if ($kotaasalbaru == $row->city_name) {
                        $kotaasalrajaongkir = $row->city_id;
                    }
                    if ($kotatujuanbaru == $row->city_name) {
                        $kotatujuanrajaongkir = $row->city_id;
                    }
                }
                if ($kotaasalrajaongkir == null || $kotatujuanrajaongkir == null) {
                    $this->session->set_flashdata('pesan', 'Data kabupaten/kota tidak di temukan di rajaongkir');
                    redirect('home');
                }
                $kurir = ['pos', 'jne', 'tiki'];
                $datacourier = array();
                foreach ($kurir as $value) {
                    $itemCourier = $this->_cost($kotaasalrajaongkir, $kotatujuanrajaongkir, $berat, $value);
                    array_push($datacourier, $itemCourier);
                }
                $data['datacourier'] = $datacourier;

                $this->load->view('kosong', $data);

                // else if ($berat == '') {
                //     ;
                // } elseif ($rajaOngkirDestination == '' || $rajaOngkirOrigin == '') {
                //     $output = 'wrongaddress';
                // }
            }
        }
    }

    private function _cost($origin, $destination, $weight, $courier)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $origin . "&destination=" . $destination . "&weight=" . $weight . "&courier=" . $courier,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: " . $this->keyrajaongkir
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = $response;
            $tes = json_decode($data);
            return $tes;
        }
    }

    public function getKabupaten()
    {
        $kabupaten = $this->input->get('term');
        if ($kabupaten) {
            $getDataKabupaten = $this->Home_model->getDataKabupaten($kabupaten);
            foreach ($getDataKabupaten as $row) {
                $results[] = array(
                    'label' => $row['provinsi'] . ', ' . $row['kabupaten'] . ', Kecamatan ' . $row['kecamatan'],
                    'kabupaten' => $row['kabupaten']
                );
                $this->output->set_content_type('application/json')->set_output(json_encode($results));
            }
        }
    }
}
