<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Busca todos os produtos com suas informações de estoque.
     * Junta as tabelas 'products' e 'stock'.
     *
     * @return array Lista de produtos.
     */
    public function get_products_with_stock()
    {
        $this->db->select('p.id, p.name, p.price, s.variation, s.quantity');
        $this->db->from('products as p');
        $this->db->join('stock as s', 's.product_id = p.id', 'left');
        $this->db->order_by('p.name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Busca um único produto pelo seu ID, com informações de estoque.
     *
     * @param int $product_id O ID do produto.
     * @return object|null O objeto do produto ou nulo se não encontrado.
     */
    public function get_product_by_id($product_id)
    {
        $this->db->select('p.id, p.name, p.price, s.id as stock_id, s.variation, s.quantity');
        $this->db->from('products as p');
        $this->db->join('stock as s', 's.product_id = p.id', 'left');
        $this->db->where('p.id', $product_id);
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Salva um novo produto e seu estoque inicial.
     * Usa transação para garantir a integridade dos dados.
     *
     * @param array $product_data Dados do produto (nome, preço).
     * @param array $stock_data Dados do estoque (variação, quantidade).
     * @return bool
     */
    public function save_product($product_data, $stock_data)
    {
        $this->db->trans_start();

        $this->db->insert('products', $product_data);
        $product_id = $this->db->insert_id();

        $stock_data['product_id'] = $product_id;
        $this->db->insert('stock', $stock_data);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Atualiza um produto e seu estoque.
     *
     * @param int $product_id
     * @param array $product_data
     * @param int $stock_id
     * @param array $stock_data
     * @return bool
     */
    public function update_product($product_id, $product_data, $stock_id, $stock_data)
    {
        $this->db->trans_start();

        // Atualiza o produto
        $this->db->where('id', $product_id);
        $this->db->update('products', $product_data);

        // Atualiza o estoque
        $this->db->where('id', $stock_id);
        $this->db->update('stock', $stock_data);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
