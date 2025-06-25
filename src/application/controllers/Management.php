<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Management extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_model');
        $this->load->library(['form_validation', 'session']);
        $this->load->helper(['url', 'form']);
    }

    /**
     * Exibe a página de gerenciamento de produtos e a lista de produtos.
     */
    public function index()
    {
        $data['products'] = $this->product_model->get_products_with_stock();
        $this->load->view('management/index_view', $data);
    }

    /**
     * Salva (cria ou atualiza) um produto.
     */
    public function save_product()
    {
        $this->form_validation->set_rules('name', 'Nome do Produto', 'required');
        $this->form_validation->set_rules('price', 'Preço', 'required');
        $this->form_validation->set_rules('quantity', 'Quantidade', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $product_id = $this->input->post('product_id');
            $stock_id = $this->input->post('stock_id');

            // CORREÇÃO: Sanitização do preço para ser mais robusta.
            $price_input = $this->input->post('price');
            // Remove tudo que não for dígito, vírgula ou ponto.
            $price_cleaned = preg_replace('/[^\d,.]/', '', $price_input);
            // Substitui a vírgula por ponto para o formato do banco de dados.
            $sanitized_price = str_replace(',', '.', $price_cleaned);

            $product_data = [
                'name' => $this->input->post('name'),
                'price' => (float) $sanitized_price, // Garante que é um número
            ];
            $stock_data = [
                'variation' => $this->input->post('variation'),
                'quantity' => $this->input->post('quantity'),
            ];

            $success = false;
            // Se houver um product_id, significa que é uma atualização.
            if ($product_id && $stock_id) {
                if ($this->product_model->update_product($product_id, $product_data, $stock_id, $stock_data)) {
                   $success = true;
                   $this->session->set_flashdata('success', 'Produto atualizado com sucesso!');
                }
            } else {
                // Caso contrário, é uma inserção de um novo produto.
                if ($this->product_model->save_product($product_data, $stock_data)) {
                    $success = true;
                    $this->session->set_flashdata('success', 'Produto criado com sucesso!');
                }
            }

            if(!$success) {
                $this->session->set_flashdata('error', 'Ocorreu um erro ao salvar o produto.');
            }
        }
        redirect('management');
    }
}
