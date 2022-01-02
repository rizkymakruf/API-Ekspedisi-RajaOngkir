<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home_model extends CI_Model
{
    public function getDataKabupaten($kabupaten)
    {
        $this->db->select('a.name as provinsi, b.name as kabupaten,c.name as kecamatan');
        $this->db->from('m_provinsi as a');
        $this->db->join('m_kabupaten as b', 'b.provinsi_id=a.id');
        $this->db->join('m_kecamatan as c', 'c.kabupaten_id=b.id');
        $this->db->like('b.name', $kabupaten);
        $this->db->or_like('c.name', $kabupaten);
        $this->db->limit(10);
        $this->db->order_by('a.id', 'ASC');
        return $this->db->get()->result_array();
    }
}
