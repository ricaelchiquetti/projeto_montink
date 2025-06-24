<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    protected $table = 'coupons';

    public function get_all() {
        $this->db->order_by('id', 'DESC');
        return $this->db->get($this->table)->result_array();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function save($id, $form_data) {
        // CORREÇÃO: Cria um array explícito apenas com os campos que
        // existem na tabela do banco de dados. Isso previne erros
        // ao tentar inserir ou atualizar campos que não existem.
        $db_data = [
            'code'            => $form_data['code'],
            'discount_type'   => $form_data['discount_type'],
            'discount_value'  => $form_data['discount_value'],
            'min_order_value' => $form_data['min_order_value'],
            'expiration_date' => $form_data['expiration_date'],
            'is_active'       => $form_data['is_active'],
        ];

        if ($id) {
            // Se um ID existe, atualiza o cupom correspondente
            $this->db->where('id', $id);
            return $this->db->update($this->table, $db_data);
        } else {
            // Se não houver ID, insere um novo cupom
            return $this->db->insert($this->table, $db_data);
        }
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
}
