<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Midtrans_model extends CI_Model
{
    public function getallproduk()
    {
        return $this->db->get('m_product')->result_array();
    }

    public function insertcart($data)
    {
        $this->db->insert('tr_cart', $data);
        return $this->db->affected_rows();
    }

    public function getallcart()
    {
        $this->db->select('a.*,b.product,b.price');
        $this->db->join('m_product as b', 'a.product_id=b.id');
        return $this->db->get_where('tr_cart as a', ['a.status' => 0])->result_array();
    }

    public function getAllTransaction()
    {
        $this->db->order_by('id','DESC');
        return $this->db->get('tr_cart_to_checkout')->result_array();
    }

    public function update($dataupdate, $where)
    {
        $this->db->update('tr_cart_to_checkout',$dataupdate,$where);
        return $this->db->affected_rows();
    }
}
