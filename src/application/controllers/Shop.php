<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Carrega bibliotecas, helpers e models necessários
        $this->load->model(['Product_model', 'Order_model']);
        $this->load->library(['session', 'form_validation']);
        $this->load->helper('url');
    }

    // Carrega a view principal da loja/ERP
    public function index() {
        $this->load->view('shop/main_view');
    }

    /* =================================================================
     * MÉTODOS DA API (CHAMADOS VIA JAVASCRIPT)
     * ================================================================= */

    // API: Retorna todos os produtos em formato JSON
    public function api_list_products() {
        header('Content-Type: application/json');
        $products = $this->Product_model->get_all_products();
        echo json_encode($products);
    }

    // API: Salva (cria ou atualiza) um produto
    public function api_save_product() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        $id = !empty($data['id']) ? $data['id'] : null;

        $product_data = ['name' => $data['name'], 'price' => $data['price']];
        $inventory_data = ['quantity' => $data['inventory']];
        
        $result_id = $this->Product_model->save_product($id, $product_data, $inventory_data);

        if ($result_id) {
            echo json_encode(['status' => 'success', 'message' => 'Produto salvo com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o produto.']);
        }
    }

    // API: Deleta um produto
    public function api_delete_product() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        if ($this->Product_model->delete_product($data['id'])) {
            echo json_encode(['status' => 'success', 'message' => 'Produto deletado com sucesso.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar o produto.']);
        }
    }
    
    // API: Retorna o estado atual do carrinho
    public function api_get_cart() {
        header('Content-Type: application/json');
        $cart = $this->Order_model->get_cart_details();
        echo json_encode($cart);
    }

    // API: Adiciona um item ao carrinho
    public function api_add_to_cart() {
        header('Content-Type: application/json');
        $data = json_decode($this->input->raw_input_stream, true);
        $product_id = $data['product_id'];
        
        $success = $this->Order_model->add_to_cart($product_id);

        if($success) {
            echo json_encode(['status' => 'success', 'message' => 'Item adicionado ao carrinho!']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Estoque insuficiente ou produto não encontrado.']);
        }
    }
    
    // API: Finaliza o pedido
    public function api_checkout() {
        header('Content-Type: application/json');
        $customer_data = json_decode($this->input->raw_input_stream, true);

        // Validação simples dos dados do cliente
        if(empty($customer_data['name']) || empty($customer_data['email']) || empty($customer_data['zipcode'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Por favor, preencha todos os dados de entrega.']);
            return;
        }

        $order_id = $this->Order_model->process_checkout($customer_data);

        if ($order_id) {
            // Aqui você pode adicionar o envio de email
            echo json_encode(['status' => 'success', 'message' => "Pedido #{$order_id} finalizado com sucesso!"]);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Não foi possível finalizar o pedido. O carrinho pode estar vazio.']);
        }
    }
}
