<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Product_model');
    }

    // Retorna todos os pedidos para o painel de gestão
    public function get_all_orders() {
        $this->db->order_by('id', 'DESC');
        return $this->db->get('orders')->result_array();
    }

    // Atualiza o status de um pedido
    public function update_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update('orders', ['status' => $status]);
    }
    
    // Deleta um pedido
    public function delete_order($id) {
        // ON DELETE CASCADE cuidará dos order_items
        $this->db->where('id', $id);
        return $this->db->delete('orders');
    }

    public function add_to_cart($product_id, $variation_id = null) {
        $cart = $this->session->userdata('cart') ?: [];
        $cart_item_key = $variation_id ? "var_{$variation_id}" : "prod_{$product_id}";
        
        $item_info = null;
        $qty_in_cart = isset($cart[$cart_item_key]) ? $cart[$cart_item_key]['quantity'] : 0;

        if ($variation_id) {
            $this->db->select('p.name, p.price, pv.variation_name, pv.variation_value, inv.quantity as inventory');
            $this->db->from('product_variations pv');
            $this->db->join('products p', 'pv.product_id = p.id');
            $this->db->join('inventory inv', 'pv.id = inv.variation_id');
            $this->db->where('pv.id', $variation_id);
            $item_info = $this->db->get()->row_array();
        } else {
            $this->db->select('p.name, p.price, inv.quantity as inventory');
            $this->db->from('products p');
            $this->db->join('inventory inv', 'p.id = inv.product_id AND inv.variation_id IS NULL');
            $this->db->where('p.id', $product_id);
            $item_info = $this->db->get()->row_array();
        }
        
        if (!$item_info || ($qty_in_cart + 1) > $item_info['inventory']) {
            return false;
        }
        
        if(isset($cart[$cart_item_key])) {
            $cart[$cart_item_key]['quantity']++;
        } else {
            $cart[$cart_item_key] = [
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'name' => $item_info['name'],
                'price' => $item_info['price'],
                'quantity' => 1,
                'variation_name' => $item_info['variation_name'] ?? null,
                'variation_value' => $item_info['variation_value'] ?? null,
            ];
        }
        $this->session->set_userdata('cart', $cart);
        return true;
    }

    public function get_cart_details() {
        $cart_items = $this->session->userdata('cart') ?: [];
        $subtotal = 0;
        $items_for_details = [];
        foreach ($cart_items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $items_for_details[] = $item;
        }

        $shipping_cost = 20.00;
        if ($subtotal > 200.00) {
            $shipping_cost = 0.00;
        } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
            $shipping_cost = 15.00;
        }
        if ($subtotal == 0) $shipping_cost = 0;

        return [
            'items' => $items_for_details,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping_cost,
            'total' => $subtotal + $shipping_cost
        ];
    }

    public function process_checkout($customer_data) {
        $cart = $this->get_cart_details();
        if(empty($cart['items'])) return false;

        $this->db->trans_start();

        $order_data = [
            'customer_name' => $customer_data['name'],
            'customer_email' => $customer_data['email'],
            'customer_zipcode' => $customer_data['zipcode'],
            'customer_address' => $customer_data['address'],
            'subtotal' => $cart['subtotal'],
            'shipping_cost' => $cart['shipping_cost'],
            'total_amount' => $cart['total'],
            'status' => 'pendente'
        ];
        $this->db->insert('orders', $order_data);
        $order_id = $this->db->insert_id();

        foreach ($cart['items'] as $item) {
            $order_item_data = [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price']
            ];
            $this->db->insert('order_items', $order_item_data);

            $this->db->set('quantity', 'quantity - ' . (int)$item['quantity'], FALSE);
            if ($item['variation_id']) {
                $this->db->where('variation_id', $item['variation_id']);
            } else {
                $this->db->where('product_id', $item['product_id'])->where('variation_id IS NULL');
            }
            $this->db->update('inventory');
        }

        $this->db->trans_complete();
        
        if ($this->db->trans_status()) {
            $this->session->unset_userdata('cart');
            return $order_id;
        }
        return false;
    }
}
