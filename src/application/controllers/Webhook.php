<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

    private $webhook_secret;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('order_model');
        $this->load->helper('security');
        $this->load->config('montink');
        $this->webhook_secret = $this->config->item('webhook_secret_token');
    }

    public function order_status()
    {
        // 1. Extrair e validar Bearer token do header Authorization
        $auth_header = $this->input->get_request_header('Authorization', TRUE);
        if (!$auth_header || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['error' => 'Token de autorização ausente ou inválido.']));
            return;
        }

        $token = $matches[1];
        if ($token !== $this->webhook_secret) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['error' => 'Acesso não autorizado.']));
            return;
        }

        // 2. Obter payload e limpar dados
        $payload = $this->input->raw_input_stream;
        $data = json_decode($payload, true);
        $data = $this->security->xss_clean($data);

        // 3. Validar dados
        if (!isset($data['order_id']) || !isset($data['status'])) {
            $this->output
                 ->set_status_header(400)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['error' => 'Payload inválido. Faltando order_id ou status.']));
            return;
        }

        $order_id = $data['order_id'];
        $status = strtolower($data['status']);

        // 4. Processar status
        try {
            if ($status === 'cancelado') {
                $this->order_model->delete_order($order_id);
                log_message('info', "Webhook: Pedido #{$order_id} removido com sucesso.");
            } else {
                $this->order_model->update_order_status($order_id, $status);
                log_message('info', "Webhook: Status do pedido #{$order_id} atualizado para '{$status}'.");
            }

            $this->output
                 ->set_status_header(200)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['success' => "Pedido #{$order_id} processado com sucesso."]));

        } catch (Exception $e) {
            log_message('error', 'Webhook Error: ' . $e->getMessage());
            $this->output
                 ->set_status_header(500)
                 ->set_content_type('application/json')
                 ->set_output(json_encode(['error' => 'Erro interno ao processar o pedido.']));
        }
    }
}
