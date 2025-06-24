<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Management extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Apenas permite acesso em ambiente de desenvolvimento. Em produção, use um sistema de login.
        if (ENVIRONMENT !== 'development') {
            show_error('Acesso restrito.', 403);
        }
        $this->load->model(['Order_model', 'Coupon_model']);
        $this->load->helper('url');
    }

    public function index() {
        $this->load->view('management/index_view');
    }

    /* =================================================================
     * API: PEDIDOS (ORDERS)
     * ================================================================= */

    public function api_list_orders() {
        header('Content-Type: application/json');
        $orders = $this->Order_model->get_all_orders();
        echo json_encode($orders);
    }
    
    public function api_update_order_status() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        $id = $data['id'];
        $status = $data['status'];
        if ($this->Order_model->update_status($id, $status)) {
            echo json_encode(['status' => 'success', 'message' => "Status do pedido #{$id} atualizado."]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Falha ao atualizar status.']);
        }
    }

    public function api_delete_order() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        if ($this->Order_model->delete_order($data['id'])) {
            echo json_encode(['status' => 'success', 'message' => 'Pedido deletado com sucesso.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Falha ao deletar pedido.']);
        }
    }
    
    /* =================================================================
     * API: CUPONS (COUPONS)
     * ================================================================= */

    public function api_list_coupons() {
        header('Content-Type: application/json');
        echo json_encode($this->Coupon_model->get_all());
    }

    public function api_get_coupon($id) {
        header('Content-Type: application/json');
        echo json_encode($this->Coupon_model->get_by_id($id));
    }

    public function api_save_coupon() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        $id = !empty($data['id']) ? $data['id'] : null;

        if ($this->Coupon_model->save($id, $data)) {
            echo json_encode(['status' => 'success', 'message' => 'Cupom salvo com sucesso.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Falha ao salvar cupom.']);
        }
    }

    public function api_delete_coupon() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        if ($this->Coupon_model->delete($data['id'])) {
            echo json_encode(['status' => 'success', 'message' => 'Cupom deletado com sucesso.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Falha ao deletar cupom.']);
        }
    }
}
