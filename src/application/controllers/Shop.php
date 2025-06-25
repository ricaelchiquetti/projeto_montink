<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->model(['product_model', 'order_model', 'coupon_model']);
        $this->load->helper(['url', 'form', 'security']);
    }

    /**
     * Exibe a página principal da loja com produtos e carrinho.
     */
    public function index()
    {
        $data['products'] = $this->product_model->get_products_with_stock();
        $data['cart'] = $this->session->userdata('cart');
        $this->load->view('shop/main_view', $data);
    }
    
    /**
     * MÉTODO DE API: Adiciona um produto ao carrinho via AJAX.
     */
    public function api_add_to_cart($product_id)
    {
        $this->output->set_content_type('application/json');
        $product = $this->product_model->get_product_by_id($product_id);

        if ($product && $product->quantity > 0) {
            $cart = $this->session->userdata('cart') ?: ['items' => [], 'subtotal' => 0, 'discount' => 0, 'coupon_code' => null];
            $item_id = $product->id;

            if (isset($cart['items'][$item_id])) {
                $cart['items'][$item_id]['qty']++;
            } else {
                $cart['items'][$item_id] = [
                    'id' => $product->id, 'name' => $product->name, 'price' => $product->price,
                    'qty' => 1, 'variation' => $product->variation,
                ];
            }
            $this->_update_cart_totals($cart);
            $this->session->set_userdata('cart', $cart);

            $this->output->set_status_header(200)->set_output(json_encode([
                'message' => 'Produto adicionado com sucesso!', 'cart' => $cart
            ]));
        } else {
            $this->output->set_status_header(400)->set_output(json_encode([
                'message' => 'Produto sem estoque ou não encontrado!'
            ]));
        }
    }

    /**
     * MÉTODO DE API: Processa a aplicação do cupom via AJAX (Axios).
     */
    public function api_apply_coupon()
    {
        $this->output->set_content_type('application/json');

        $coupon_code = $this->input->post('coupon_code');
        $cart = $this->session->userdata('cart');

        if (empty($cart) || empty($cart['items'])) {
            $this->output->set_status_header(400)->set_output(json_encode(['message' => 'Carrinho vazio.']));
            return;
        }

        $coupon = $this->coupon_model->get_coupon_by_code($coupon_code);

        if (!$coupon) {
            $this->output->set_status_header(404)->set_output(json_encode(['message' => 'Cupom inválido ou expirado.']));
            return;
        }

        if ($cart['subtotal'] < $coupon->min_purchase_amount) {
            $this->output->set_status_header(400)->set_output(json_encode(['message' => 'Valor mínimo para este cupom não atingido.']));
            return;
        }

        $discount = 0;
        if ($coupon->discount_type == 'percentage') {
            $discount = ($cart['subtotal'] * $coupon->value) / 100;
        } else {
            $discount = $coupon->value;
        }

        $cart['discount'] = $discount;
        $cart['coupon_code'] = $coupon->code;
        
        $this->_update_cart_totals($cart);
        $this->session->set_userdata('cart', $cart);

        $this->output->set_status_header(200)->set_output(json_encode(['message' => 'Cupom aplicado com sucesso!', 'cart' => $cart]));
    }
    
    /**
     * Finaliza o pedido, salva no banco e envia e-mail.
     */
    public function place_order()
    {
        $this->form_validation->set_rules('customer_name', 'Nome', 'required|trim');
        $this->form_validation->set_rules('customer_email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('customer_cep', 'CEP', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('shop');
            return;
        }

        $cart = $this->session->userdata('cart');
        if (empty($cart) || empty($cart['items'])) {
            $this->session->set_flashdata('error', 'Seu carrinho está vazio!');
            redirect('shop');
            return;
        }

        $orderData = [
            'customer_name'     => $this->input->post('customer_name'),
            'customer_email'    => $this->input->post('customer_email'),
            'customer_cep'      => $this->input->post('customer_cep'),
            'customer_address'  => $this->input->post('customer_address'),
            'subtotal'          => $cart['subtotal'],
            'shipping_cost'     => $cart['shipping_cost'],
            'discount_amount'   => $cart['discount'] ?? 0,
            'total'             => $cart['total'],
            'status'            => 'pending',
            'coupon_code'       => $cart['coupon_code'] ?? null,
        ];
        
        $order_id = $this->order_model->save_order($orderData, $cart['items']);

        if ($order_id) {
            $this->_send_confirmation_email($orderData, $cart, $order_id);
            $this->session->unset_userdata('cart');
            $this->session->set_flashdata('success', "Pedido #{$order_id} realizado com sucesso! Verifique seu e-mail.");
        } else {
            $this->session->set_flashdata('error', 'Houve um erro ao processar seu pedido. O estoque pode ter mudado. Tente novamente.');
        }

        redirect('shop');
    }
    
    /**
     * Busca o endereço através do CEP.
     */
    public function get_address_by_cep($cep)
    {
        $cep = preg_replace("/[^0-9]/", "", $cep);
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $output = curl_exec($ch);
        curl_close($ch);

        $this->output
             ->set_content_type('application/json')
             ->set_output($output);
    }

    private function _update_cart_totals(&$cart)
    {
        $subtotal = 0;
        if (!empty($cart['items'])) {
            foreach ($cart['items'] as &$item) {
                $item['subtotal'] = $item['price'] * $item['qty'];
                $subtotal += $item['subtotal'];
            }
        }
        
        $cart['subtotal'] = $subtotal;
        $cart['shipping_cost'] = $this->_calculate_shipping($subtotal);
        $discount = $cart['discount'] ?? 0;
        $cart['total'] = ($subtotal - $discount) + $cart['shipping_cost'];
    }

    private function _calculate_shipping($subtotal)
    {
        if ($subtotal > 200) return 0.00;
        if ($subtotal >= 52.00 && $subtotal <= 166.59) return 15.00;
        return 20.00;
    }

    private function _send_confirmation_email($orderData, $cart, $order_id)
    {
        $this->load->library('email');
        $this->email->from('nao-responda@sua-loja.com', 'Sua Loja');
        $this->email->to($orderData['customer_email']);
        $this->email->subject('Confirmação do Pedido #' . $order_id);
        
        $message = "<h1>Obrigado por seu pedido!</h1><p>Seu pedido #{$order_id} foi recebido e está sendo processado.</p>";
        $this->email->message($message);

        if (!$this->email->send()) {
            log_message('error', 'Falha ao enviar e-mail de confirmação: ' . $this->email->print_debugger());
        }
    }
}
