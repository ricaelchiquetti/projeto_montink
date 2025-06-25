<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Busca um cupom pelo código.
     * @param string $code
     * @return object|null
     */
    public function get_coupon_by_code($code)
    {
        $this->db->where('code', $code);
        $this->db->where('is_active', 1);
        $this->db->where('valid_until >', date('Y-m-d H:i:s'));
        $query = $this->db->get('coupons');
        return $query->row();
    }
    
    /**
     * Busca todos os cupons do banco.
     * @return array
     */
    public function get_all_coupons()
    {
        $query = $this->db->get('coupons');
        return $query->result();
    }

    /**
     * Salva um novo cupom.
     * @param array $data
     * @return bool
     */
    public function save_coupon($data)
    {
        return $this->db->insert('coupons', $data);
    }
}
