<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Retorna todos os produtos com seu estoque principal.
    public function get_all_products() {
        $this->db->select('p.id, p.name, p.price, inv.quantity as inventory');
        $this->db->from('products as p');
        $this->db->join('inventory as inv', 'p.id = inv.product_id AND inv.variation_id IS NULL', 'left');
        $this->db->order_by('p.id', 'DESC');
        return $this->db->get()->result_array();
    }

    // Retorna um único produto pelo seu ID.
    public function get_product_by_id($id) {
        $this->db->select('p.id, p.name, p.price, inv.quantity as inventory');
        $this->db->from('products as p');
        $this->db->join('inventory as inv', 'p.id = inv.product_id AND inv.variation_id IS NULL', 'left');
        $this->db->where('p.id', $id);
        return $this->db->get()->row_array();
    }

    // Salva (cria ou atualiza) um produto e seu estoque.
    public function save_product($id, $product_data, $inventory_data) {
        $this->db->trans_start();

        if ($id) {
            $this->db->where('id', $id)->update('products', $product_data);
            $this->db->where('product_id', $id)->where('variation_id IS NULL')->update('inventory', $inventory_data);
            $result_id = $id;
        } else {
            $this->db->insert('products', $product_data);
            $result_id = $this->db->insert_id();
            $inventory_data['product_id'] = $result_id;
            $this->db->insert('inventory', $inventory_data);
        }
        
        $this->db->trans_complete();
        return $this->db->trans_status() ? $result_id : false;
    }

    // Deleta um produto (o DB cuida das chaves estrangeiras com ON DELETE CASCADE).
    public function delete_product($id) {
        $this->db->trans_start();
        
        // Limpa o item do carrinho na sessão, se existir.
        $cart = $this->session->userdata('cart') ?: [];
        unset($cart[$id]);
        $this->session->set_userdata('cart', $cart);

        $this->db->where('id', $id)->delete('products');

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
