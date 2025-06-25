<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupons extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Carrega models, helpers e bibliotecas necessários
        $this->load->model('coupon_model');
        $this->load->library(['form_validation', 'session']); // CORREÇÃO: Adicionada a library 'session'
        $this->load->helper(['url', 'form']);
    }

    /**
     * Exibe a página de gerenciamento de cupons com a lista de cupons existentes.
     */
    public function index()
    {
        $data['coupons'] = $this->coupon_model->get_all_coupons();
        $this->load->view('coupons/index_view', $data);
    }

    /**
     * Salva um novo cupom no banco de dados.
     */
    public function save_coupon()
    {
        // Regras de validação
        $this->form_validation->set_rules('code', 'Código', 'required|trim|is_unique[coupons.code]');
        $this->form_validation->set_rules('value', 'Valor', 'required|decimal');
        $this->form_validation->set_rules('valid_until', 'Válido até', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = [
                'code' => $this->input->post('code'),
                'discount_type' => $this->input->post('discount_type'),
                'value' => $this->input->post('value'),
                'min_purchase_amount' => $this->input->post('min_purchase_amount') ?: 0.00,
                'valid_until' => $this->input->post('valid_until'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            ];

            if ($this->coupon_model->save_coupon($data)) {
                $this->session->set_flashdata('success', 'Cupom salvo com sucesso!');
            } else {
                $this->session->set_flashdata('error', 'Erro ao salvar o cupom.');
            }
        }
        redirect('coupons');
    }
}
