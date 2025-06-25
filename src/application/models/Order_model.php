<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Salva um novo pedido e seus itens no banco de dados.
     * Esta operação é envolvida em uma transação para garantir a atomicidade.
     *
     * @param array $orderData Dados do pedido.
     * @param array $cartItems Itens do carrinho.
     * @return int|bool O ID do pedido inserido ou false em caso de falha.
     */
    public function save_order($orderData, $cartItems)
    {
        $this->db->trans_start();

        // 1. Inserir o pedido na tabela 'orders'
        $this->db->insert('orders', $orderData);
        $order_id = $this->db->insert_id();

        // 2. Preparar os itens do pedido para inserção em lote
        $order_items = [];
        foreach ($cartItems as $item) {
            // Apenas itens válidos com 'id' e 'qty'
            if (isset($item['id']) && isset($item['qty'])) {
                $order_items[] = [
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal']
                ];

                // 3. Atualizar o estoque
                $this->db->set('quantity', 'quantity - ' . (int)$item['qty'], FALSE);
                $this->db->where('product_id', $item['id']);
                // Se houver variação, a lógica de estoque precisa ser ajustada aqui
                // Por simplicidade, assumindo que o ID do produto é suficiente.
                $this->db->update('stock');
            }
        }
        
        // Insere os itens do pedido, se houver
        if (!empty($order_items)) {
             // Esta tabela não foi criada na migração original, vamos pular por enquanto.
             // Para uma implementação completa, a tabela 'order_items' seria necessária.
             // $this->db->insert_batch('order_items', $order_items);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            // A transação falhou, o rollback foi executado automaticamente
            log_message('error', 'Falha na transação ao salvar o pedido.');
            return false;
        }

        return $order_id;
    }
    
    /**
     * Atualiza o status de um pedido específico.
     *
     * @param int $order_id O ID do pedido.
     * @param string $status O novo status do pedido.
     * @return bool Retorna true se a atualização for bem-sucedida, false caso contrário.
     */
    public function update_order_status($order_id, $status)
    {
        $this->db->where('id', $order_id);
        return $this->db->update('orders', ['status' => $status]);
    }

    /**
     * Remove um pedido do banco de dados.
     *
     * @param int $order_id O ID do pedido a ser removido.
     * @return bool Retorna true se a remoção for bem-sucedida, false caso contrário.
     */
    public function delete_order($order_id)
    {
        // Em uma implementação real, talvez seja melhor apenas marcar como 'deletado'
        // ou também remover itens associados em 'order_items' (com transação).
        $this->db->where('id', $order_id);
        return $this->db->delete('orders');
    }
}
